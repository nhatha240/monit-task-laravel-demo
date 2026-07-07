<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;
/**
 * @property int $id
 * @property string $title
 * @property string $description
 * @property string $status
 * @property int|null $assigned_to
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $due_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
#[Fillable(['title', 'description', 'status', 'assigned_to', 'due_date', 'updated_at','created_by'])]
#[Guarded(['id', 'created_by', 'created_at'])]
class Task extends Model
{


    protected function casts(): array
    {
        return [
            'due_date' => 'datetime',
        ];
    }
}
