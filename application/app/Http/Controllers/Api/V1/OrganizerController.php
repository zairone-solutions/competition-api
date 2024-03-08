<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\PostReportResource;
use App\Models\PostReport;
use Illuminate\Http\Request;

class OrganizerController extends BaseController
{

    public function reports(Request $request)
    {
        switch ($request->type) {
            case 'cleared':
                return $this->resData(PostReportResource::collection(auth()->user()->got_reports()->cleared()->paginate(20)));
            default:
                return $this->resData(PostReportResource::collection(auth()->user()->got_reports()->paginate(20)));
        }
    }

    public function clear_report_toggle(Request $request, PostReport $postReport)
    {
        if (auth()->user()->id !== $postReport->organizer_id) {
            return $this->resMsg(["error" => "Only organizer can clear a report."], "authentication", 400);
        }

        $postReport->update(['cleared' => !$postReport->cleared]);

        return $this->resMsg(PostReportResource::make($postReport));
    }
}
