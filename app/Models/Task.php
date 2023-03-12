<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'start_date', 'end_date', 'done', 'approved', 'done_date', 'approved_date', 'created_by', 'updated_by'
    ];

    public function items()
    {
        return $this->hasMany(Item::class);
    }
}
