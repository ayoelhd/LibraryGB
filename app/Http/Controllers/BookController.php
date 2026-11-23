<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookStoreRequest;
use App\Http\Requests\BookUpdateRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::query();

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        return BookResource::collection($query->get());
    }

    public function store(BookStoreRequest $request)
    {
        $book = Book::create($request->validated());

        return BookResource::make($book)->response()->setStatusCode(201);
    }

    public function show(Book $book)
    {
        return BookResource::make($book->load('category'));
    }

    public function update(BookUpdateRequest $request, Book $book)
    {
        $book->update($request->validated());

        return BookResource::make($book);
    }

    public function destroy(Book $book)
    {
        $book->delete();

        return response()->json(['message' => 'deleted']);
    }
}
