<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskLists extends Model
{
    //
    use HasFactory;
    protected $table = 'TASK_LISTS';
    protected $primaryKey = 'nTaskListNo'; 

    protected $fillable = [
        'nTaskListNo',
        'cTaskListName',
        'cTaskListsColor'
    ];  

    public function tasks(): HasMany
    {
        return $this->hasMany(Tasks::class, 'nTaskListNo');
    }
}
