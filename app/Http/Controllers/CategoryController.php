<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::whereNull('deleted_at')
            ->orderBy('name', 'desc')
            ->get();

        if ($categories) {
            return $this->sendResponse($categories, 'Get Categories successfully.');
        } else {
            return $this->sendError('Failed.', ['error' => 'Get Categories failed'], 500);
        }
    }

    /**
     * Create a new resource.
     */
    public function create(Request $request)
    {
        if (!json_decode(Auth::user()->scopes)->rol == "sudo" || !json_decode(Auth::user()->scopes)->rol == "SuperAdmin") {
            return $this->sendError('You do not have permissions to access this resource.', ['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $categories = Category::create([
            'name' => $request->name,
            'description' => $request->description,
            'created_by' => Auth::user()->id
        ]);

        if ($categories) {
            return $this->sendResponse($categories, 'Created Categories successfully.');
        } else {
            return $this->sendError('Failed.', ['error' => 'Created Categories failed'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $category = Category::find($request->category_id);

        if ($category) {
            return $this->sendResponse($category, 'Get Category successfully.');
        } else {
            return $this->sendError('Failed.', ['error' => 'Get Category failed'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {

        $category = Category::find($request->category_id);

        $category->name = $request->name;
        $category->description = $request->description;
        $category->updated_by = Auth::user()->id;
        $category->save();

        if ($category) {
            return $this->sendResponse($category, 'Updated Category successfully.');
        } else {
            return $this->sendError('Failed.', ['error' => 'Updated Category failed'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $category = Category::find($request->category_id);

        $category->deleted_at = Carbon::now();
        $category->updated_by = Auth::user()->id;
        $category->save();

        if ($category) {
            return $this->sendResponse($category, 'Deleted Category successfully.');
        } else {
            return $this->sendError('Failed.', ['error' => 'Deleted Category failed'], 500);
        }
    }
}
