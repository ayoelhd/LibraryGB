<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Borrowing;
use App\Http\Requests\BorrowRequest;

class BorrowingsController extends Controller
{
    // Customer: list my borrowed books, each with a "pivot" object (dates + is_returned)
    public function index(Request $request)
    {
        $user = $request->user();

        return response()->json(['data' => $user->books]);
    }

    // Customer: borrow a book. The customer comes from the token, never from the body.
    public function borrow(BorrowRequest $request)
    {
        
    
        $data = $request->validated();


            // A customer may have at most 3 books out at once
        $openCount = $request->user()->books()
            ->wherePivot('is_returned', false)
            ->count();

        if ($openCount >= 3) {
            return response()->json([
                'message' => 'You already have 3 open borrowings',
            ], 422);
        }

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

        $request->user()->books()->attach(
            $data['book_id'],
            [
                'borrowing_date' => $data['borrowing_date'],
                'return_date'    => $data['return_date'],
                'is_returned'    => false,
            ]
        );

        return response()->json(['message' => 'Book borrowed successfully'], 201);
    }




    // Customer: cancel an open borrowing that hasn't started yet. History stays.
    public function destroy(Request $request, int $id)
    {
        $deleted = $request->user()->books()
            ->wherePivot('is_returned', false)
            ->wherePivot('borrowing_date', '>', now()->toDateString())
            ->detach($id);

        if ($deleted === 0) {
            return response()->json([
                'message' => 'You can only cancel a borrowing before its start date',
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


        // Staff: every borrowing, with its customer and book. ?is_returned=0 shows only open ones.
    public function all(Request $request)
    {
        $query = Borrowing::with(['customer', 'book']);

        if ($request->has('is_returned')) {
            $query->where('is_returned', $request->is_returned);
        }

        return response()->json($query->get());
    }


        // Staff: open borrowings whose return date has already passed
    public function overdue()
    {
        $late = Borrowing::with(['customer', 'book'])
            ->where('is_returned', false)
            ->where('return_date', '<', now()->toDateString())
            ->get();

        return response()->json($late);
    }
}
