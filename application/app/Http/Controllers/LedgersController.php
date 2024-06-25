<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\Payment;
use App\Models\Ledger;
use Carbon\Carbon;
use DataTables;

class LedgersController extends Controller
{
    /**
     * Display a listing of the users via DataTables.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function showledgers(Request $request)
    {   
        if ($request->ajax()) {
            $data = Ledger::select('*')->with('user_by'); // Eager load the user's full name
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('user_by', function($row){
                    return $row->user_by ? $row->user_by->full_name : 'N/A'; // Check if suggested_by anyone
                })
                ->make(true);
        }
        return view('ledgers.all-ledgers');
    }


}
