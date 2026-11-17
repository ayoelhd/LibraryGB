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
}
