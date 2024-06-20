<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\CompetitionHelper;
use App\Helpers\RuleHelper;
use App\Http\Resources\CompetitionResource;
use App\Http\Resources\PostCommentResource;
use App\Http\Resources\PostImageResource;
use App\Http\Resources\PostJustified;
use App\Http\Resources\PostJustifiedResource;
use App\Http\Resources\PostMediaResource;
use App\Http\Resources\PostObjectionResource;
use App\Http\Resources\PostOrganizerResource;
use App\Http\Resources\PostReportResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\PostVoterResource;
use App\Jobs\Media\UnlinkS3Media;
use App\Jobs\UploadImageToS3;
use App\Jobs\UploadVideoToS3;
use App\Mail\Post\PostApproveAlert;
use App\Mail\Post\PostObjectionAlert;
use App\Mail\Post\PostPublishAlert;
use App\Mail\Post\PostReportAlert;
use App\Mail\Post\PostVoteAlert;
use App\Models\Competition;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostImage;
use App\Models\PostMedia;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Image;
use FFMpeg;
use Illuminate\Support\Facades\URL;

class PostController extends BaseController
{
    private function getPostRules($key = NULL)
    {
        $rules = [];
        $competition = Setting::where("key", "post")->first();
        foreach ($competition->children()->get() as $rule) {
            if ($key && $key == $rule->key)
                return $rule->value;
            $rules[$rule->key] = $rule->value;
        }
        return $rules;
    }
    public function all(Request $request, Competition $competition)
    {
        if (auth()->id() == $competition->organizer_id) {
            return $this->resData(PostOrganizerResource::collection($competition->posts()->paginate(20)));
        }
        if (auth()->user()->votes()->where("competition_id", $competition->id)->count()) {
            return $this->resData(PostVoterResource::collection($competition->posts()->approved()->visible()->paginate(20)));
        }

        return $this->resData(PostResource::collection($competition->posts()->approved()->visible()->paginate(20)));
    }
    public function winner(Request $request)
    {
        return $this->resData(PostResource::collection(Post::where("won", 1)->limit(5)->get()));
    }
    public function voted(Request $request)
    {

        $user = auth()->user();
        if ($request->has("username")) {
            if ($findUser = User::where(["username" => $request->get("username")])->first()) {
                $user = $findUser;
            }
        }

        $votedPosts = collect();

        if ($votes = $user->votes()->get()) {
            foreach ($votes as $vote) {
                $votedPosts->add($vote->post);
            }
        }

        return $this->resData(PostResource::collection($votedPosts));
    }
    public function personal(Request $request)
    {

        $user = auth()->user();
        if ($request->has("username")) {
            if ($findUser = User::where(["username" => $request->get("username")])->first()) {
                $user = $findUser;
            }
        }
        return $this->resData(PostJustifiedResource::collection($user->posts()->voted()->paginate(20)));
    }
    public function store_text(Request $request, Competition $competition)
    {

        try {
            $competition_rules = RuleHelper::rules("competition");

            if (
                $competition->posts()->where("user_id", auth()->id())->created()->count()
                || $competition->posts()->where("user_id", auth()->id())->voted()->count()

            ) {
                return $this->resMsg(["error" => "You have already posted in the competition."], "validation", 403);
            }

            if ($competition->posts()->where("user_id", auth()->id())->draft()->count() == $competition_rules['max_drafts_allowed']) {
                return $this->resMsg(["error" => "You can only create " . $competition_rules['max_drafts_allowed'] . " drafts per competition."], "validation", 403);
            }

            $rules = [
                "description" => ["nullable", "max:450"]
            ];
            $errors = $this->reqValidate($request->all(), $rules);
            if ($errors)
                return $errors;

            DB::beginTransaction();

            $post = $competition->posts()->create([
                'user_id' => auth()->id(),
                'description' => $request->description,
                'state' => "draft"
            ]);

            DB::commit();

            return $this->resData(PostResource::make($post));
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }

    public function store_image(Request $request, Competition $competition, Post $post)
    {

        try {
            $post_rules = RuleHelper::rules("post");

            if (!$post->id) {
                $post = $competition->posts()->create([
                    'user_id' => auth()->id(),
                    'description' => "",
                    'state' => "draft"
                ]);
            }

            if ($post->media()->count() == $post_rules['no_of_images_allowed']) {
                return $this->resMsg(["error" => "You can only upload " . $post_rules['no_of_images_allowed'] . " media files per post."], "validation", 403);
            }

            $rules = [
                'image' => ["required", "image", "", "mimes:jpeg,png,jpg", "max:" . ((int) $post_rules['max_image_size'] * 1024)],
            ];
            $errors = $this->reqValidate($request->all(), $rules, [
                "image.size" => "You can only upload " . $post_rules['no_of_images_allowed'] . " images.",
                "image.image" => "Please upload a valid image.",
                "image.mimes" => "The image must be a file of type: jpeg, png, jpg.",
                "image.max" => "The image must not be greater than " . $post_rules['max_image_size'] . "mb.",
            ]);
            if ($errors)
                return $errors;

            DB::beginTransaction();

            $image = $request->file('image');

            $temporaryFilePath = $image->store('public/uploads/temporary_images');

            $media = $post->media()->create(['media' => asset(str_replace("public", "storage", $temporaryFilePath)), "mime_type" => $image->extension()]);

            $fileName = uniqid() . '.' . $image->getClientOriginalExtension();
            $path = "images/posts/" . $fileName;

            UploadImageToS3::dispatch($competition, $media, $path, $temporaryFilePath);

            DB::commit();

            return $this->resData(PostResource::make($post));
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
    public function store_video(Request $request, Competition $competition, Post $post)
    {

        try {
            $post_rules = RuleHelper::rules("post");

            if (!$post->id) {
                $post = $competition->posts()->create([
                    'user_id' => auth()->id(),
                    'description' => "",
                    'state' => "draft"
                ]);
            }

            if ($post->media()->count() == $post_rules['no_of_images_allowed']) {
                return $this->resMsg(["error" => "You can only upload " . $post_rules['no_of_images_allowed'] . " media files per post."], "validation", 403);
            }

            $rules = [
                'video' => [
                    'required',
                    'file',
                    'mimes:mp4,avi,mov', // Adjust the allowed video formats as needed
                    'max:' . ((int) $post_rules['max_video_size'] * 1024), // Convert to kilobytes
                ],
            ];
            $errors = $this->reqValidate($request->all(), $rules, [
                'video.required' => 'Please upload a video.',
                'video.file' => 'Please upload a valid file.',
                'video.mimes' => 'The video must be a file of type: mp4, avi, mov.',
                'video.max' => 'The video must not be greater than ' . $post_rules['max_video_size'] . 'MB.',
            ]);
            if ($errors) {
                return $errors;
            }

            DB::beginTransaction();

            $video = $request->file('video');

            $temporaryFilePath = $video->store('public/uploads/temporary_videos');

            $media = $post->media()->create(['media' => asset(str_replace("public", "storage", $temporaryFilePath)), "type" => "video", "mime_type" => $video->extension()]);

            $fileName = uniqid() . '.' . $video->getClientOriginalExtension();
            $path = "videos/posts/" . $fileName;

            UploadVideoToS3::dispatch($competition, $media, $path, $temporaryFilePath);

            DB::commit();

            return $this->resData(PostResource::make($post));
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
    public function update(Request $request, Competition $competition, Post $post)
    {
        $post_rules = RuleHelper::rules("post");
        $rules = [
            "description" => ["nullable", "max:450"],
        ];
        $errors = $this->reqValidate($request->all(), $rules, );
        if ($errors)
            return $errors;

        $post->update([
            'description' => $request->description
        ]);

        return $this->resData(CompetitionResource::make($competition));
    }

    public function delete(Request $request, Competition $competition, Post $post)
    {
        try {
            DB::beginTransaction();

            if ($post->media()->count()) {

                $media = $post->media()->get();

                foreach ($media as $item) {

                    UnlinkS3Media::dispatch($item->media);
                    $item->delete();
                }
            }

            $post->delete();

            DB::commit();

            return $this->resData(CompetitionResource::make($competition));

        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
    public function delete_media(Request $request, Competition $competition, PostMedia $post_media)
    {
        try {
            DB::beginTransaction();

            UnlinkS3Media::dispatch($post_media->media);

            $post_media->delete();

            DB::commit();

            return $this->resMsg(['success' => "Image deleted successfully."]);

        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
    public function publish(Request $request, Competition $competition, Post $post)
    {
        try {
            DB::beginTransaction();

            $post->update(['state' => "created", 'hidden' => "0", "approved_at" => date("Y-m-d H:i:s")]);

            if ($competition->posts()->where("user_id", auth()->id())->where("id", "!=", $post->id)->count()) {

                $drafts = $competition->posts()->where("user_id", auth()->id())->where("id", "!=", $post->id)->get();
                foreach ($drafts as $draft) {

                    if ($draft->media()->count()) {

                        $media = $draft->media()->get();
                        foreach ($media as $item) {

                            UnlinkS3Media::dispatch($item->media);
                            $item->delete();
                        }
                    }

                    $draft->delete();
                }
            }

            @Mail::to($post->user)->send(new PostPublishAlert(['organizer' => $competition->organizer, 'competition' => $competition]));
            $this->triggerNotification($post->user->id, $post->user->notification_token, "Post published for approval!", 'published_post', "A user has published a post for approval in " . $this->cName($competition->slug), ['id' => $post->id]);

            DB::commit();

            return $this->resData(CompetitionResource::make($competition));

        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
    public function approve(Request $request, Competition $competition, Post $post)
    {

        $post->update(['approved_at' => date("Y-m-d H:i:s")]);
        if ($post->objection)
            $post->objection->update(['cleared' => 1]);

        // Email & Notifications
        @Mail::to($post->user)->send(new PostApproveAlert(['organizer' => $competition->organizer, 'competition' => $competition]));
        $this->triggerNotification($post->user->id, $post->user->notification_token, "Post Approved!", 'approved', "Your post has been approved by the organizer of " . $this->cName($competition->slug), ['id' => $post->id]);

        return $this->resData(PostOrganizerResource::make($post));
    }
    public function toggle_show(Request $request, Competition $competition, Post $post)
    {

        $post->update(['hidden' => !$post->hidden]);

        return $this->resData(PostOrganizerResource::make($post));
    }
    public function object(Request $request, Competition $competition, Post $post)
    {
        if (strtotime($competition->voting_start_at) < time()) {
            return $this->resMsg(["error" => "Objections can only be made before the voting starts."], "validation", 403);
        }
        $rules = ["description" => ["required", "min:6", "max:450", "bad_word"]];
        $errors = $this->reqValidate($request->all(), $rules, ['description.required' => "You must provide the reason.", 'bad_word' => 'The :attribute cannot contain any inappropriate word.',]);
        if ($errors)
            return $errors;

        $post->update(['approved_at' => NULL]);
        if ($post->objection)
            $post->objection->update(['description' => $request->description, 'cleared' => 0]);
        else
            $post->objection()->create(['description' => $request->description, 'cleared' => 0]);

        // Email & Notifications
        @Mail::to($post->user)->send(new PostObjectionAlert(['objection' => $post->objection, 'competition' => $competition]));
        $this->triggerNotification($post->user->id, $post->user->notification_token, "Post objected!", 'objection', $this->cName($competition->slug) . " organizer has put an objection on your post.", ['id' => $post->id]);

        return $this->resData(PostObjectionResource::make($post->objection));
    }
    public function vote(Request $request, Competition $competition, Post $post)
    {
        if (auth()->user()->votes()->where("competition_id", $competition->id)->count()) {
            return $this->resMsg(["error" => "Your vote has already been casted."], "authentication", 400);
        }
        if (strtotime($competition->announcement_at) < time()) {
            return $this->resMsg(["error" => "Competition already announced!"], "validation", 403);
        }

        $vote = $post->votes()->create(['competition_id' => $competition->id, "voter_id" => auth()->id()]);

        // Email & Notification
        @Mail::to($post->user)->send(new PostVoteAlert(['organizer' => $competition->organizer, 'competition' => $competition, 'vote' => $vote]));
        $this->triggerNotification($post->user->id, $post->user->notification_token, "Vote casted!", 'voted', "Your voted in " . $this->cName($competition->slug), ['id' => $post->id], NULL);

        return $this->resData(PostVoterResource::make($post));
    }
    public function report(Request $request, Competition $competition, Post $post)
    {
        if (auth()->user()->reports()->where("post_id", $post->id)->count()) {
            return $this->resMsg(["error" => "You have already reported this post."], "authentication", 400);
        }

        $rules = ["description" => ["nullable", "min:6", "max:450", "bad_word"]];
        $errors = $this->reqValidate($request->all(), $rules);
        if ($errors)
            return $errors;

        $report = $post->reports()->create(['reporter_id' => auth()->id(), 'organizer_id' => $competition->organizer->id, 'description' => $request->description]);

        // Email & Notifications
        @Mail::to($post->user)->send(new PostReportAlert(['report' => $report, 'competition' => $post->competition, 'user' => auth()->user()]));
        $this->triggerNotification($post->user->id, $post->user->notification_token, "Post Reported!", 'reported', auth()->user()->username . " has reported a post in " . $this->cName($competition->slug) . ".", ['id' => $report->id]);

        return $this->resData(PostReportResource::make($report));
    }

    public function comments_all(Request $request, Post $post)
    {
        try {
            if (auth()->id() == $post->organizer_id)
                $comments = PostCommentResource::collection($post->comments()->coms()->default()->paginate(15));
            else
                $comments = PostCommentResource::collection($post->comments()->coms()->visible()->default()->paginate(15));

            return $this->resData($comments);
        } catch (\Throwable $th) {
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
    public function comment_replies_all(Request $request, Post $post, PostComment $post_comment)
    {
        try {
            if (auth()->id() == $post->organizer_id)
                $replies = PostCommentResource::collection($post_comment->replies()->default()->paginate(15));
            else {
                if ($post_comment->hidden) {
                    return $this->resMsg(["error" => "Replies of hidden comments can not be shown."], "validation", 403);
                }
                $replies = PostCommentResource::collection($post_comment->replies()->visible()->default()->paginate(15));
            }
            return $this->resData($replies);
        } catch (\Throwable $th) {
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
    public function comments_store(Request $request, Post $post)
    {
        try {
            $rules = ['text' => "required|min:1|max:450"];
            $errors = $this->reqValidate($request->all(), $rules, ['bad_word' => 'The :attribute cannot contain any inappropriate word.']);
            if ($errors)
                return $errors;

            DB::beginTransaction();

            $reply = auth()->user()->competition_comments()->create([
                "competition_id" => $post->id,
                "text" => $request->text,
            ]);

            DB::commit();

            return $this->resData(PostCommentResource::make($reply));
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
    public function comment_replies(Request $request, Post $post, PostComment $post_comment)
    {
        try {
            $rules = ['text' => "required|min:1|max:450"];
            $errors = $this->reqValidate($request->all(), $rules, ['bad_word' => 'The :attribute cannot contain any inappropriate word.']);
            if ($errors)
                return $errors;

            DB::beginTransaction();

            $reply = auth()->user()->competition_comments()->create([
                "competition_id" => $post->id,
                "comment_id" => $post_comment->id,
                "type" => "reply",
                "text" => $request->text,
            ]);
            DB::commit();

            return $this->resData(PostCommentResource::make($reply));
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
    public function comment_update(Request $request, Post $post, PostComment $post_comment)
    {
        try {
            if (auth()->id() !== $post->organizer_id) {
                return $this->resMsg(["error" => "Only organizer can update a comment."], "authentication", 400);
            }

            DB::beginTransaction();

            $post_comment->update($request->only(["hidden"]));

            DB::commit();

            return $this->resData(PostCommentResource::make($post_comment));
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
}
