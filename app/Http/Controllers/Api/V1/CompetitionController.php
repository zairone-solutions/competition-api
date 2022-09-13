<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\CompetitionResource;
use App\Models\Competition;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

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
    }

    private function getCompetitionRules($key = NULL)
    {
        $rules = [];
        $competition = Setting::where("key", "competition")->first();
        foreach ($competition->children()->get() as $rule) {
            if ($key && $key == $rule->key) return $rule->value;
            $rules[$rule->key] = $rule->value;
        }
        return $rules;
    }
    private function calculateCompetitionCost(float $participants)
    {
        $cost_per_participant = (float) $this->getCompetitionRules("cost_per_participant");
        return $cost_per_participant * $participants;
    }
    public function store(Request $request)
    {
        $competition_rules = $this->getCompetitionRules();

        $rules = [
            'category_id' => ["required", Rule::exists('categories', "id")->where(function ($query) use ($request) {
                return $query->where(['id' => $request->category_id, "verified" => 1]);
            }),],
            'title' => ["required", "max:50", "min:3"],
            'entry_fee' => ["required", "numeric", "min:" . $competition_rules["min_entry_fee"], "max:" . $competition_rules["max_prize_money"]],
            'prize_money' => ["required", "numeric", "min:" . $competition_rules["min_prize_money"], "max:" . $competition_rules["max_prize_money"]],
            'participants_allowed' => ["required", "numeric", "min:" . $competition_rules["min_participants_allowed"], "max:" . $competition_rules["max_participants_allowed"]],
            "announcement_at" => ["required", "after_or_equal:" .  $competition_rules['min_competition_days'] . " days", "before_or_equal:" . $competition_rules['max_competition_days'] . " days"],
            "voting_start_at" => ["required", "after_or_equal:" .  $competition_rules['voting_delay_days'] . " days"],
        ];
        $errors = $this->reqValidate($request->all(), $rules, [
            'category_id.exists' => "Invalid category."
        ]);
        if ($errors) return $errors;


        $slug_matches = Competition::where("title", $request->title)->count();
        $competition = auth()->user()->competitions()->create([
            "category_id" => $request->category_id,
            "title" => $request->title,
            "slug" => $slug_matches ? Str::slug($request->title) . "-" . ($slug_matches + 1) : Str::slug($request->title),
            "cost" => $this->calculateCompetitionCost($request->participants_allowed),
            "entry_fee" => $request->entry_fee,
            "prize_money" => $request->prize_money,
            "participants_allowed" => $request->participants_allowed,
            "announcement_at" => date("Y-m-d H:i:s", strtotime($request->announcement_at)),
            "voting_start_at" => date("Y-m-d H:i:s", strtotime($request->voting_start_at)),
        ]);
        return $this->resData(CompetitionResource::make($competition));
    }
    public function update(Request $request, Competition $competition)
    {
        if ($competition->published_at) {
            return $this->resMsg(["message" => "Published competition can not be edited."], "authentication", 400);
        }

        $competition_rules = $this->getCompetitionRules();

        $rules = [
            'category_id' => ["required", Rule::exists('categories', "id")->where(function ($query) use ($request) {
                return $query->where(['id' => $request->category_id, "verified" => 1]);
            }),],
            'title' => ["required", "max:50", "min:3"],
            'entry_fee' => ["required", "numeric", "min:" . $competition_rules["min_entry_fee"], "max:" . $competition_rules["max_prize_money"]],
            'prize_money' => ["required", "numeric", "min:" . $competition_rules["min_prize_money"], "max:" . $competition_rules["max_prize_money"]],
            'participants_allowed' => ["required", "numeric", "min:" . $competition_rules["min_participants_allowed"], "max:" . $competition_rules["max_participants_allowed"]],
            "announcement_at" => ["required"],
            "voting_start_at" => ["required"],
        ];
        $errors = $this->reqValidate($request->all(), $rules, [
            'category_id.exists' => "Invalid category."
        ]);
        if ($errors) return $errors;
        if ((strtotime($request->announcement_at) > strtotime("+" . $competition_rules['max_competition_days'] . " days") ||
                strtotime($request->announcement_at) < strtotime("+" . $competition_rules['min_competition_days'] . " days"))
            ||  strtotime($request->announcement_at) < (strtotime($request->voting_start_at) + (24 * 60 * 60))
        ) {
            return $this->resMsg(["message" => "Invalid announcement date."], "validation", 400);
        }
        if (
            strtotime($request->announcement_at) < (strtotime($request->voting_start_at) + (24 * 60 * 60))
        ) {
            return $this->resMsg(["message" => "Invalid voting date."], "validation", 400);
        }

        $slug_matches = Competition::where("title", $request->title)->where("id", "!=", $competition->id)->count();

        $competition->update([
            "category_id" => $request->category_id,
            "title" => $request->title,
            "slug" => $slug_matches ? Str::slug($request->title) . "-" . ($slug_matches + 1) : Str::slug($request->title),
            "cost" => $this->calculateCompetitionCost($request->participants_allowed),
            "entry_fee" => $request->entry_fee,
            "prize_money" => $request->prize_money,
            "participants_allowed" => $request->participants_allowed,
            "announcement_at" => date("Y-m-d H:i:s", strtotime($request->announcement_at)),
            "voting_start_at" => date("Y-m-d H:i:s", strtotime($request->voting_start_at)),
        ]);
        return $this->resData(CompetitionResource::make($competition));
    }
    public function publish(Request $request, Competition $competition)
    {
        if (auth()->user()->id !== $competition->organizer_id) {
            return $this->resMsg(["message" => "Only organizer can publish a competition."], "authentication", 400);
        }
        if (!$competition->payment_verified_at) {
            return $this->resMsg(["message" => "Payment not verified yet."], "authentication", 400);
        }
        if (auth()->user()->competitions()->where('id', $competition->id)->first()->published_at) {
            return $this->resMsg(["message" => "Competiton already published."], "authentication", 400);
        }

        if (strtotime($competition->voting_start_at) < strtotime('now')) {
            return $this->resMsg(["message" => "Voting date has expired. Please update to publish."], "validation", 400);
        }
        if (strtotime($competition->announcement_at) < strtotime('now')) {
            return $this->resMsg(["message" => "Announcement date has expired. Please update to publish."], "validation", 400);
        }

        $competition->published_at = date("Y-m-d H:i:s", strtotime("now"));
        $competition->update();

        return $this->resMsg(["success" => "Competition published successfully."]);
    }
    public function participate(Request $request, Competition $competition)
    {

        if (auth()->user()->id == $competition->organizer_id) {
            return $this->resMsg(["message" => "Organizer can not participate in the competition."], "authentication", 400);
        }
        if (auth()->user()->participations()->where('competition_id', $competition->id)->first()) {
            return $this->resMsg(["message" => "You have already participated."], "authentication", 400);
        }

        $competition->participants()->create(['participant_id' => auth()->user()->id]);

        return $this->resMsg(["success" => "You have participated successfully."]);
    }
}
