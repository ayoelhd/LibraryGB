<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

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
            'borrowing_date' => ['required', 'date'],
            'return_date'    => ['required', 'date'],
        ]);

        // attach($ids, $pivotColumns): first the book id, then the extra columns
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

    // Customer: cancel an OPEN borrowing of a book. Returned ones stay as history.
    public function destroy($id)
    {
        $user = auth()->user();

        $deleted = $user->books()
            ->wherePivot('is_returned', false)
            ->detach($id);

        if ($deleted === 0) {
            return response()->json([
                'message' => 'You have no open borrowing for this book',
            ], 404);
        }

        return response()->json(['message' => 'done']);
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
