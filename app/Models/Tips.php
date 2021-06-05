<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Category;

class Tips extends Model
{
    use HasFactory;

    protected $perPage = 10;

    public function category() {
        return $this->hasOne(Category::class, 'id', 'category');
    }
}
