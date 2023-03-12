<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id','title', 'priority', 'done'
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
