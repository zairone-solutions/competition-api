<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\RuleHelper;
use App\Http\Resources\PostImageResource;
use App\Http\Resources\PostJustified;
use App\Http\Resources\PostJustifiedResource;
use App\Http\Resources\PostMediaResource;
use App\Http\Resources\PostObjectionResource;
use App\Http\Resources\PostOrganizerResource;
use App\Http\Resources\PostReportResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\PostVoterResource;
use App\Jobs\UploadImageToS3;
use App\Jobs\UploadVideoToS3;
use App\Mail\Post\PostApproveAlert;
use App\Mail\Post\PostObjectionAlert;
use App\Mail\Post\PostReportAlert;
use App\Mail\Post\PostVoteAlert;
use App\Models\Competition;
use App\Models\Post;
use App\Models\PostImage;
use App\Models\Setting;
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
            if ($key && $key == $rule->key) return $rule->value;
            $rules[$rule->key] = $rule->value;
        }
        return $rules;
    }
    public function all(Request $request, Competition $competition)
    {
        if (auth()->user()->id == $competition->organizer_id) {
            return $this->resData(PostOrganizerResource::collection($competition->posts()->paginate(20)));
        }
        if (auth()->user()->votes()->where("competition_id", $competition->id)->count()) {
            return $this->resData(PostVoterResource::collection($competition->posts()->approved()->visible()->paginate(20)));
        }

        return $this->resData(PostResource::collection($competition->posts()->approved()->visible()->paginate(20)));
    }
    public function personal(Request $request)
    {
        return $this->resData(PostJustifiedResource::collection(auth()->user()->posts()->paginate(20)));
    }
    public function store_text(Request $request, Competition $competition)
    {

        try {
            $competition_rules = RuleHelper::rules("competition");

            if (
                $competition->posts()->where("user_id", auth()->user()->id)->created()->count()
                || $competition->posts()->where("user_id", auth()->user()->id)->voted()->count()

            ) {
                return $this->resMsg(["error" => "You have already posted in the competition."], "validation", 403);
            }

            if ($competition->posts()->where("user_id", auth()->user()->id)->draft()->count() == $competition_rules['max_drafts_allowed']) {
                return $this->resMsg(["error" => "You can only create " . $competition_rules['max_drafts_allowed'] . " drafts per competition."], "validation", 403);
            }

            $rules = [
                "description" => ["nullable", "max:450", "bad_word"]
            ];
            $errors = $this->reqValidate($request->all(), $rules, [
                'bad_word' => 'The :attribute cannot contain any inappropriate word.'
            ]);
            if ($errors) return $errors;

            DB::beginTransaction();

            $post = $competition->posts()->create([
                'user_id' => auth()->user()->id,
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
            if ($errors) return $errors;

            DB::beginTransaction();

            $image = $request->file('image');

            $temporaryFilePath = $image->store('public/uploads/temporary_images');

            $media = $post->media()->create(['media' => asset(str_replace("public", "storage", $temporaryFilePath)), "mime_type" => $image->extension()]);

            $fileName = uniqid() . '.' . $image->getClientOriginalExtension();
            $path = "images/posts/" . $fileName;

            UploadImageToS3::dispatch($media, $path, $temporaryFilePath);

            DB::commit();

            return $this->resData(PostMediaResource::make($media));
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
    public function store_video(Request $request, Competition $competition, Post $post)
    {

        try {
            $post_rules = RuleHelper::rules("post");

            if ($post->media()->count() == $post_rules['no_of_images_allowed']) {
                return $this->resMsg(["error" => "You can only upload " . $post_rules['no_of_images_allowed'] . " media files per post."], "validation", 403);
            }

            $rules = [
                'video' => [
                    'required',
                    'file',
                    'mimes:mp4,avi,mov', // Adjust the allowed video formats as needed
                    'max:' . ((int)$post_rules['max_video_size'] * 1024), // Convert to kilobytes
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

            UploadVideoToS3::dispatch($media, $path, $temporaryFilePath);

            DB::commit();

            return $this->resData(PostMediaResource::make($media));
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
    public function update(Request $request, Competition $competition, Post $post)
    {
        $post_rules = RuleHelper::rules("post");
        $rules = [
            "description" => ["nullable", "max:450", "bad_word"],
            // 'image' => ["nullable", "array", "size:3"],
            // 'image.*' => ["nullable", "image", "mimes:jpeg,png,jpg", "max:" . ((int) $post_rules['max_image_size'] * 1024)],
        ];
        $errors = $this->reqValidate($request->all(), $rules, [
            'bad_word' => 'The :attribute cannot contain any inappropriate word.',
            // "image.size" => "You can only upload 3 images.",
            // "image.*.image" => "Please upload a valid image.",
            // "image.*.mimes" => "The image must be a file of type: jpeg, png, jpg.",
            // "image.*.max" => "The image must not be greater than " . $post_rules['max_image_size'] . "mb.",
        ]);
        if ($errors) return $errors;

        $post->update([
            'description' => $request->description
        ]);

        // $images = $request->file("image");
        // if ($request->hasFile("image")) {
        //     foreach ($images as $image) {
        //         $imageName = time() . "_" . rand(1111, 9999) . "." . $image->getClientOriginalExtension();
        //         $imgFile = Image::make($image->getRealPath());
        //         $imgFile->orientate();
        //         $imgFile->resize(720, 480, function ($constraint) {
        //             $constraint->aspectRatio();
        //             $constraint->upsize();
        //         })->save(public_path('storage/posts/') .  $imageName);

        //         // $img->storeAs('public/posts', $imageName);
        //         $post->images()->create(['image' => "posts/" . $imageName, "mime_type" => $image->extension()]);
        //     }
        // }
        return $this->resData(PostResource::make($post));
    }
    public function upload_image(Request $request, Competition $competition, Post $post)
    {
        $post_rules = RuleHelper::rules("post");

        if ($post->images()->count() >= (int) $post_rules['no_of_images_allowed']) {
            return $this->resMsg(["error" => "Maximum images limit reached! Please delete any image to upload a new one."], "authentication", 400);
        }
        $rules = [
            'image' => ["required", "image", "mimes:jpeg,png,jpg", "max:" . ((int) $post_rules['max_image_size'] * 1024)],
        ];
        $errors = $this->reqValidate($request->all(), $rules, [
            "image.image" => "Please upload a valid image.",
            "image.mimes" => "The image must be a file of type: jpeg, png, jpg.",
            "image.max" => "The image must not be greater than " . $post_rules['max_image_size'] . "mb.",
        ]);
        if ($errors) return $errors;

        $image = $request->file("image");
        $imageName = time() . "_" . rand(1111, 9999) . "." . $image->getClientOriginalExtension();
        $imgFile = Image::make($image->getRealPath());
        $imgFile->orientate();
        $imgFile->resize((int) $post_rules['image_resize_width'] ?? 720, (int) $post_rules['image_resize_height'] ?? 480, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        })->save(public_path('storage/posts/') .  $imageName);

        // $img->storeAs('public/posts', $imageName);
        $post_image = $post->images()->create(['image' => "posts/" . $imageName, "mime_type" => $image->extension()]);

        return $this->resData(PostImageResource::make($post_image));
    }
    public function delete_image(Request $request, Competition $competition, PostImage $post_image)
    {
        Storage::delete("public/" . $post_image->image);
        $post_image->delete();
        return $this->resMsg(['success' => "Image deleted successfully."]);
    }
    public function approve(Request $request, Competition $competition, Post $post)
    {

        $post->update(['approved_at' => date("Y-m-d H:i:s")]);
        if ($post->objection) $post->objection->update(['cleared' => 1]);

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
            return $this->resMsg(["error" => "Objections can only be made before the voting starts."], "validation", 400);
        }
        $rules = ["description" => ["required", "min:6", "max:450", "bad_word"]];
        $errors = $this->reqValidate($request->all(), $rules, ['description.required' => "You must provide the reason.", 'bad_word' => 'The :attribute cannot contain any inappropriate word.',]);
        if ($errors) return $errors;

        $post->update(['approved_at' => NULL]);
        if ($post->objection) $post->objection->update(['description' => $request->description, 'cleared' => 0]);
        else $post->objection()->create(['description' => $request->description, 'cleared' => 0]);

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
            return $this->resMsg(["error" => "Competition already announced!"], "validation", 400);
        }

        $vote = $post->votes()->create(['competition_id' => $competition->id, "voter_id" => auth()->user()->id]);

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
        if ($errors) return $errors;

        $report = $post->reports()->create(['reporter_id' => auth()->user()->id, 'organizer_id' => $competition->organizer->id, 'description' => $request->description]);

        // Email & Notifications
        @Mail::to($post->user)->send(new PostReportAlert(['report' => $report, 'competition' => $post->competition, 'user' => auth()->user()]));
        $this->triggerNotification($post->user->id, $post->user->notification_token, "Post Reported!", 'reported', auth()->user()->username . " has reported a post in " .  $this->cName($competition->slug) . ".", ['id' => $report->id]);

        return $this->resData(PostReportResource::make($report));
    }
}
