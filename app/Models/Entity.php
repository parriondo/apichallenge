<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    use HasFactory;

    protected $fillable = ['api', 'description', 'link', 'category_id'];

    public function category() {
        return $this->belongsTo(Category::class);
    }
}
