<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'name',
        'ISBN',
        'category_id',
        'author',
        'publish_date',
        'publisher',
    ];

    // Each book belongs to one category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Many-to-many: a book is borrowed by many customers, through the borrowings table
    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'borrowings')
            ->withPivot('borrowing_date', 'return_date', 'is_returned')
            ->withTimestamps();
    }
}
