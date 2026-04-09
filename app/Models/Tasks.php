<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tasks extends Model
{
    //
    use HasFactory;
    protected $table = 'TASKS';

    protected $fillable = [
        'nTaskNo',
        'cTaskName',
        'cTaskDescription',
        'cTaskPriority',
        'cCompleted',
        'nTaskListNo'
    ];

    protected $casts = [
        'cCompleted' => 'boolean',
    ];

    public function tasks(): BelongsTo
    {
        return $this->belongsTo(TaskLists::class, 'nTaskListNo');
    }
}
