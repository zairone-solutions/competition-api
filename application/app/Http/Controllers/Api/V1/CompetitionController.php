<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\RuleHelper;
use App\Http\Resources\CompetitionCommentResource;
use App\Http\Resources\CompetitionResource;
use App\Jobs\CompetitionParticipatedJob;
use App\Jobs\CompetitionPublishedJob;
use App\Mail\Competition\CompetitionParticipation;
use App\Mail\Competition\CompetitionParticipationAlert;
use App\Mail\Competition\CompetitionPublished;
use App\Models\Competition;
use App\Models\CompetitionComment;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class CompetitionController extends BaseController
{
    private function voted()
    {
        $voted = [];
        foreach (auth()->user()->votes()->get() as $vote) {
            $voted[] = $vote->competition()->first();
        }
        return $voted;
    }
    private function participated()
    {
        $participated = [];
        foreach (auth()->user()->participations()->get() as $vote) {
            $participated[] = $vote->competition()->first();
        }
        return $participated;
    }

    public function all(Request $request)
    {

        try {
            switch ($request->get("type")) {
                case 'voted':
                    return $this->resData(CompetitionResource::collection($this->voted()));
                case 'participated':
                    return $this->resData(CompetitionResource::collection($this->participated()));
                case 'organized':
                    return $this->resData(CompetitionResource::collection(auth()->user()->competitions()->get()));
                default:
                    return $this->resData([
                        'organized' => CompetitionResource::collection(auth()->user()->competitions()->get()),
                        'participated' => CompetitionResource::collection($this->participated()),
                        'voted' => CompetitionResource::collection($this->voted())
                    ]);
            }
        } catch (\Throwable $th) {
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
    private function calculateCompetitionCost(float $participants)
    {
        $cost_per_participant = (float) $this->getCompetitionRules("cost_per_participant");
        return $cost_per_participant * $participants;
    }
    private function generateCompetitionSlug($title)
    {
        $slug = Str::slug($title);
        if (Competition::where("slug", $slug)->count() == 0) {
            return $slug;
        }

        do {
            $slug = Str::slug($title) . "-" .  rand(111, 9999);
        } while (Competition::where("slug", $slug)->count() != 0);

        return $slug;
    }
    public function store(Request $request)
    {
        try {
            $competition_rules = RuleHelper::rules("competition");

            $rules = [
                'category_id' => ["required", Rule::exists('categories', "id")->where(function ($query) use ($request) {
                    return $query->where(['id' => $request->category_id, "verified" => 1]);
                }),],
                'title' => ["required", "max:50", "min:3", "bad_word"],
                'description' => ["nullable", "max:450", "bad_word"],
                'entry_fee' => ["required", "numeric", "min:" . $competition_rules["min_entry_fee"], "max:" . $competition_rules["max_prize_money"]],
                'prize_money' => ["required", "numeric", "min:" . $competition_rules["min_prize_money"], "max:" . $competition_rules["max_prize_money"]],
                'participants_allowed' => ["required", "numeric", "min:" . $competition_rules["min_participants_allowed"], "max:" . $competition_rules["max_participants_allowed"]],
                "announcement_at" => ["required", "after_or_equal:" .  $competition_rules['min_competition_days'] . " days", "before_or_equal:" . $competition_rules['max_competition_days'] . " days"],
                "voting_start_at" => ["required", "after_or_equal:" .  $competition_rules['voting_delay_days'] . " days"],
            ];
            $errors = $this->reqValidate($request->all(), $rules, [
                'category_id.exists' => "Invalid category.",
                'bad_word' => 'The :attribute cannot contain any inappropriate word.',
                'voting_start_at.after_or_equal' => "The voting date must be after {$competition_rules['voting_delay_days']} days from today.",
                'announcement_at.after_or_equal' => "The announcement date must be {$competition_rules['min_competition_days']} days after starting the competition.",
                'announcement_at.before_or_equal' => "The announcement date must be {$competition_rules['max_competition_days']} days from now."
            ]);
            if ($errors) return $errors;

            DB::beginTransaction();

            $competition = auth()->user()->competitions()->create([
                "category_id" => $request->category_id,
                "title" => $request->title,
                "description" => $request->description,
                "slug" => $this->generateCompetitionSlug($request->title),
                "cost" => $this->calculateCompetitionCost($request->participants_allowed),
                "paid" => (int) $request->entry_fee > 0,
                "entry_fee" => $request->entry_fee,
                "prize_money" => $request->prize_money,
                "participants_allowed" => $request->participants_allowed,
                "announcement_at" => date("Y-m-d H:i:s", strtotime($request->announcement_at)),
                "voting_start_at" => date("Y-m-d H:i:s", strtotime($request->voting_start_at)),
            ]);

            DB::commit();

            return $this->resData(CompetitionResource::make($competition));
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
    public function update(Request $request, Competition $competition)
    {
        try {
            if ($competition->published_at) {
                return $this->resMsg(["error" => "Published competition can not be edited."], "authentication", 400);
            }

            $competition_rules = RuleHelper::rules("competition");

            $rules = [
                'category_id' => ["required", Rule::exists('categories', "id")->where(function ($query) use ($request) {
                    return $query->where(['id' => $request->category_id, "verified" => 1]);
                }),],
                'title' => ["required", "max:50", "min:3", "bad_word"],
                'description' => ["nullable", "max:450", "bad_word"],
                "paid" => (int) $request->entry_fee > 0,
                'entry_fee' => ["required", "numeric", "min:" . $competition_rules["min_entry_fee"], "max:" . $competition_rules["max_prize_money"]],
                'prize_money' => ["required", "numeric", "min:" . $competition_rules["min_prize_money"], "max:" . $competition_rules["max_prize_money"]],
                'participants_allowed' => ["required", "numeric", "min:" . $competition_rules["min_participants_allowed"], "max:" . $competition_rules["max_participants_allowed"]],
                "announcement_at" => ["required"],
                "voting_start_at" => ["required"],
            ];
            $errors = $this->reqValidate($request->all(), $rules, [
                'category_id.exists' => "Invalid category.",
                'bad_word' => 'The :attribute cannot contain any inappropriate word.',
            ]);
            if ($errors) return $errors;

            // time validations
            if (strtotime($request->announcement_at) > strtotime("+" . $competition_rules['max_competition_days'] . " days")) {
                return $this->resMsg(["error" => "Announcement date must be before " . $competition_rules['max_competition_days'] . " days."], "validation", 400);
            }
            if (strtotime($request->announcement_at) < strtotime("+" . $competition_rules['min_competition_days'] . " days")) {
                return $this->resMsg(["error" => "Announcement date must be after " . $competition_rules['min_competition_days'] . " days."], "validation", 400);
            }
            if (strtotime($request->voting_start_at) < (strtotime($request->voting_start_at))) {
                return $this->resMsg(["error" => "Voting date must be after " . $competition_rules['voting_delay_days'] . " days."], "validation", 400);
            }

            $slug_matches = Competition::where("slug", Str::slug($request->title))->where("id", "!=", $competition->id)->count();

            DB::beginTransaction();

            $competition->update([
                "category_id" => $request->category_id,
                "title" => $request->title,
                "description" => $request->description,
                "slug" => $slug_matches ? Str::slug($request->title) . "-" . ($slug_matches + 1) : Str::slug($request->title),
                "cost" => $this->calculateCompetitionCost($request->participants_allowed),
                "entry_fee" => $request->entry_fee,
                "prize_money" => $request->prize_money,
                "participants_allowed" => $request->participants_allowed,
                "announcement_at" => date("Y-m-d H:i:s", strtotime($request->announcement_at)),
                "voting_start_at" => date("Y-m-d H:i:s", strtotime($request->voting_start_at)),
            ]);

            DB::commit();

            return $this->resData(CompetitionResource::make($competition));
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
    public function publish(Request $request, Competition $competition)
    {
        try {
            $competition_rules = RuleHelper::rules("competition");

            if (auth()->user()->id !== $competition->organizer_id) {
                return $this->resMsg(["error" => "Only organizer can publish a competition."], "validation", 400);
            }
            if (!$competition->payment_verified_at) {
                return $this->resMsg(["error" => "Payment not verified yet."], "validation", 400);
            }
            if ($competition->isPublished()) {
                return $this->resMsg(["error" => "Competition already published."], "validation", 400);
            }

            if (strtotime($competition->voting_start_at) <= (strtotime('now') + (((int) $competition_rules['voting_delay_days'] + 1) * 24 * 60 * 60))) {
                return $this->resMsg(["error" => "Voting date must be after " . $competition_rules['voting_delay_days'] . " days. Please update to publish."], "validation", 400);
            }
            if (strtotime($competition->announcement_at) <= (strtotime('now') + ((int) $competition_rules['min_competition_days']  * 24 * 60 * 60))) {
                return $this->resMsg(["error" => "Announcement date must be after " . $competition_rules['min_competition_days'] . " days. Please update to publish."], "validation", 400);
            }

            $competition->published_at = date("Y-m-d H:i:s", strtotime("now"));

            DB::beginTransaction();

            $competition->update();

            // Dispatch job
            CompetitionPublishedJob::dispatch(auth()->user(), $competition);

            DB::commit();
            return $this->resMsg(["success" => "Competition published successfully."]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
    public function participate(Request $request, Competition $competition)
    {
        try {

            if (auth()->user()->id == $competition->organizer_id) {
                return $this->resMsg(["error" => "Organizer can not participate in the competition."], "authorization", 400);
            }
            if (!$competition->isPublished()) {
                return $this->resMsg(["error" => "Competition has not been published yet."], "authorization", 400);
            }
            if (auth()->user()->participations()->where('competition_id', $competition->id)->first()) {
                return $this->resMsg(["error" => "You have already participated."], "authentication", 400);
            }

            DB::beginTransaction();

            $competition->participants()->create(['participant_id' => auth()->user()->id]);

            auth()->user()->update(['type' => "participant"]);

            // Dispatch job
            CompetitionParticipatedJob::dispatch(auth()->user(), $competition);

            DB::commit();

            return $this->resMsg(["success" => "You have participated successfully."]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }

    public function comments_all(Request $request, Competition $competition)
    {
        try {
            if (auth()->user()->id == $competition->organizer_id)
                $comments = CompetitionCommentResource::collection($competition->comments()->coms()->default()->paginate(15));
            else
                $comments = CompetitionCommentResource::collection($competition->comments()->coms()->visible()->default()->paginate(15));

            return $this->resData($comments);
        } catch (\Throwable $th) {
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
    public function comment_replies_all(Request $request, Competition $competition, CompetitionComment $competition_comment)
    {
        try {
            if (auth()->user()->id == $competition->organizer_id)
                $replies = CompetitionCommentResource::collection($competition_comment->replies()->default()->paginate(15));
            else {
                if ($competition_comment->hidden) {
                    return $this->resMsg(["error" => "Replies of hidden comments can not be shown."], "validation", 400);
                }
                $replies = CompetitionCommentResource::collection($competition_comment->replies()->visible()->default()->paginate(15));
            }
            return $this->resData($replies);
        } catch (\Throwable $th) {
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
    public function comments_store(Request $request, Competition $competition)
    {
        try {
            $rules = ['text' => "required|min:1|max:450|bad_word"];
            $errors = $this->reqValidate($request->all(), $rules, ['bad_word' => 'The :attribute cannot contain any inappropriate word.']);
            if ($errors) return $errors;

            DB::beginTransaction();

            $reply = auth()->user()->competition_comments()->create([
                "competition_id" => $competition->id,
                "text" => $request->text,
            ]);

            DB::commit();

            return $this->resData(CompetitionCommentResource::make($reply));
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
    public function comment_replies(Request $request, Competition $competition, CompetitionComment $competition_comment)
    {
        try {
            $rules = ['text' => "required|min:1|max:450|bad_word"];
            $errors = $this->reqValidate($request->all(), $rules, ['bad_word' => 'The :attribute cannot contain any inappropriate word.']);
            if ($errors) return $errors;

            DB::beginTransaction();

            $reply = auth()->user()->competition_comments()->create([
                "competition_id" => $competition->id,
                "comment_id" => $competition_comment->id,
                "type" => "reply",
                "text" => $request->text,
            ]);
            DB::commit();

            return $this->resData(CompetitionCommentResource::make($reply));
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
    public function comment_update(Request $request, Competition $competition, CompetitionComment $competition_comment)
    {
        try {
            if (auth()->user()->id !== $competition->organizer_id) {
                return $this->resMsg(["error" => "Only organizer can update a comment."], "authentication", 400);
            }

            DB::beginTransaction();

            $competition_comment->update($request->only(["hidden"]));

            DB::commit();

            return $this->resData(CompetitionCommentResource::make($competition_comment));
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->resMsg(['error' => $th->getMessage()], 'server', 500);
        }
    }
}
