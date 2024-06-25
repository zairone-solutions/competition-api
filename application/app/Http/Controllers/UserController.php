<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Http\Requests\UserRequest;
use DataTables;


class UserController extends Controller
{
    /**
     * Display a listing of the users
     *
     * @param  \App\Models\User  $model
     * @return \Illuminate\View\View
     */
    public function index(User $model)
    {
        return view('users.index');
    }

    /**
     * Display a listing of the users via DataTables.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function showuser(Request $request)
    {   
        if ($request->ajax()) {
            $data = User::select('*')->get(); 
            return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('action', function($row){
                        $btn = '<a href="' . route('edituser', $row->id) . '" class="edit btn btn-primary btn-sm">Edit</a> ';
                        $btn .= '<a href="' . route('deleteuser', $row->id) . '" class="edit btn btn-danger btn-sm delete-user" >Delete</a>';
                        $btn .= '<a href="' . route('allledgers') . '" class="edit btn btn-info btn-sm" >...</a>';
                        return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
        }
        return view('all-users.all-users');
    }

    public function adduser()
    {
        return view('all-users.add-user');
    }

    public function storeuser(Request $request)
    {
        $validatedData = $request->validate([
            'username' => 'required',
            'email' => 'required|email|unique:users,email',
            'full_name' => 'required',
            // 'phone_no' => 'nullable',
            'phone_no' => 'nullable|string|max:20', 
            // 'type' => 'required|in:voter,organizer,participant,admin',
            'type' => 'required|in:voter,organizer,participant',
            'password' => 'required|min:6',
        ]);

        // Set phone code from request to validated data
        $validatedData['phone_code'] = $request->phone_code;

        // Hash the password before saving to the database
        $validatedData['password'] = bcrypt($request->password);

        // Create new user
        if (User::create($validatedData)) {
            Session::flash('success', 'User added successfully.');
        } else {
            Session::flash('error', 'Failed to add user.');
        }

        return redirect()->route('allusers');
    }

    public function edituser($id)
    {
        $user = User::findOrFail($id);
        return view('all-users.edit-user', compact('user'));
    }

    public function updateuser(Request $request, $id)
    {
        $validatedData = $request->validate([
            'username' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'full_name' => 'required',
            'phone_no' => 'nullable',
            //'type' => 'required|in:voter,organizer,participant,admin',
            'type' => 'required|in:voter,organizer,participant',
        ]);

        // Set phone code from request to validated data
        $validatedData['phone_code'] = $request->phone_code;

        // Hash the password before saving to the database if it's provided
        if ($request->filled('password')) {
            $validatedData['password'] = bcrypt($request->password);
        }

        // Update user
        if (User::whereId($id)->update($validatedData)) {
            Session::flash('success', 'User updated successfully.');
        } else {
            Session::flash('error', 'Failed to update user.');
        }

        return redirect()->route('allusers');
    }


    public function deleteuser($id)
    {
        if (User::findOrFail($id)->delete()) {
            Session::flash('success', 'User deleted successfully.');
        } else {
            Session::flash('error', 'Failed to delete user.');
        }

        return redirect()->route('allusers');
    }


}
