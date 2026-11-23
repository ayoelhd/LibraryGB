<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\Book;

class BorrowingsController extends Controller
{
    // Customer: list my borrowed books, each with a "pivot" object (dates + is_returned)
    public function index()
    {
        $user = auth()->user();

        return response()->json(['data' => $user->books]);
    }

    // Customer: borrow a book. The customer comes from the token, never from the body.
    public function borrow(Request $request)
    {
        $data = $request->validate([
            'book_id'        => ['required', 'exists:books,id'],
            'borrowing_date' => ['required', 'date', 'after_or_equal:today'],
            'return_date'    => ['required', 'date', 'after:borrowing_date'],
        ]);

        // Is this book already out (not returned) during these dates?
        $isTaken = Book::find($data['book_id'])->customers()
            ->wherePivot('is_returned', false)
            ->wherePivot('borrowing_date', '<=', $data['return_date'])
            ->wherePivot('return_date', '>=', $data['borrowing_date'])
            ->exists();

        if ($isTaken) {
            return response()->json([
                'message' => 'This book is already borrowed on these dates',
            ], 422);
        }

        auth()->user()->books()->attach(
            $data['book_id'],
            [
                'borrowing_date' => $data['borrowing_date'],
                'return_date'    => $data['return_date'],
                'is_returned'    => false,
            ]
        );

        return response()->json(['message' => 'Book borrowed successfully'], 201);
    }

    // Staff: mark a customer's open borrowing of a book as returned
    public function markAsReturned(Request $request)
    {
        $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'book_id'     => ['required', 'exists:books,id'],
        ]);

        $customer = Customer::find($request->customer_id);

        // wherePivot filters on the borrowings table, not on books
        $borrowing = $customer->books()
            ->wherePivot('book_id', $request->book_id)
            ->wherePivot('is_returned', false)
            ->first();

        if (! $borrowing) {
            return response()->json([
                'message' => 'This customer has no open borrowing for this book',
            ], 404);
        }

        // Change is_returned on the pivot row only, for the open borrowing
        $customer->books()
            ->wherePivot('is_returned', false)
            ->updateExistingPivot($request->book_id, [
                'is_returned' => true,
            ]);

        return response()->json([
            'message'        => 'Book marked as returned',
            'customer_id'    => $customer->id,
            'book_id'        => $borrowing->id,
            'borrowing_date' => $borrowing->pivot->borrowing_date,
        ]);
    }
}
