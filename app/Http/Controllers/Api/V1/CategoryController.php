<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends BaseController
{
    public function request(Request $request)
    {
        $rules = ['title' => "required|min:3|max:25|unique:categories"];
        $errors = $this->reqValidate($request->all(), $rules, ['title.unique' => "Category exists already."]);
        if ($errors) return $errors;

        auth()->user()->category_suggests()->create(['title' => $request->title, "slug" => str_slug($request->title)]);

        return $this->resMsg(['success' => "Category creation request has been sent successfully."]);
    }
    public function all(Request $request)
    {
        $condition = [[]];
        if ($request->has("s")) {
            $condition = ['title', 'LIKE', '%' . $request->get("s") . '%'];
        }
        $cats = Category::where(...$condition)->verified()->get(['id', 'title', 'slug']);
        return $this->resData($cats);
    }
}
