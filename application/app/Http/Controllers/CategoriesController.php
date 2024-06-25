<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use App\Models\Category;
use DataTables;

class CategoriesController extends Controller
{
   
       /**
     * Display a listing of the users via DataTables.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function showcategory(Request $request)
    {   
        if ($request->ajax()) {
            $data = Category::select('*')->with('suggested_by'); // Include suggested_by relation
            return DataTables::of($data)
                    ->addIndexColumn()
                    ->addColumn('suggested_by', function($row){
                        return $row->suggested_by ? $row->suggested_by->full_name : 'N/A'; // Check if suggested_by anyone
                    })
                    ->addColumn('action', function($row){
                        $btn = '<a href="' . route('editcategory', $row->id) . '" class="edit btn btn-primary btn-sm">Edit</a> ';
                        $btn .= '<a href="' . route('deletecategory', $row->id) . '" class="edit btn btn-danger btn-sm delete-user" >Delete</a>';
                        return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
        }
        return view('all-categories.all-categories');
    }

    

    public function addcategory()
    {
        $users = User::all(); // Fetch all users
        return view('all-categories.add-category', compact('users'));
    }

    public function storecategory(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories',
            'verified' => 'nullable|boolean',
            'suggest_id' => 'nullable|exists:users,id',
        ]);

        // Create new category
        if (Category::create($validatedData)) {
            Session::flash('success', 'Category added successfully.');
        } else {
            Session::flash('error', 'Failed to add category.');
        }

        return redirect()->route('allcategories');
    }

    public function editcategory($id)
    {
        $category = Category::findOrFail($id);
        $users = User::all(); // Fetch all users
        return view('all-categories.edit-category', compact('category', 'users'));
    }

    public function updatecategory(Request $request, $id)
    {
        // Fetch the category by ID
        $category = Category::findOrFail($id);
    
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug,' . $category->id, // Use $category->id
            'verified' => 'nullable|boolean',
            'suggest_id' => 'nullable|exists:users,id',
        ]);
    
        // Update category
        if ($category->update($validatedData)) {
            Session::flash('success', 'Category updated successfully.');
        } else {
            Session::flash('error', 'Failed to update category.');
        }
    
        return redirect()->route('allcategories');
    }
    


    public function deletecategory($id)
    {
        if (Category::findOrFail($id)->delete()) {
            Session::flash('success', 'Category deleted successfully.');
        } else {
            Session::flash('error', 'Failed to delete category.');
        }

        return redirect()->route('allcategories');
    }



}
