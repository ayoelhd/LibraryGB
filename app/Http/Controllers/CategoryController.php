<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryStoreRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        return CategoryResource::collection(Category::all());
    }

    public function store(CategoryStoreRequest $request)
    {
        $category = Category::create($request->validated());

        return CategoryResource::make($category)->response()->setStatusCode(201);
    }

    public function show(Category $category)
    {
        return CategoryResource::make($category->load('books'));
    }

    public function update(CategoryUpdateRequest $request, Category $category)
    {
        $category->update($request->validated());

        return CategoryResource::make($category);
    }

    public function destroy(Category $category)
    {
        // A category that still has books can't be deleted (foreign key)
        if ($category->books()->exists()) {
            return response()->json([
                'message' => 'This category still has books. Delete or move them first.',
            ], 409);
        }

        $category->delete();

        return response()->json(['message' => 'deleted']);
    }
}
