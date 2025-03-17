<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ManageTasks extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'manage_tasks_sample';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
        'category',
        'status',
        'days',
        'document',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['created_at', 'updated_at'];

       /**
     * Handle both accessor and mutator for 'status'
     */
    protected function status(): Attribute
    {
        return Attribute::make(
            // Accessor: Convert numeric status to human-readable text
            get: fn($value) => $value == 1 ? 'Completed' : 'Pending',

            // Mutator: Convert text status to numeric value before saving
            set: fn($value) => strtolower($value) === 'completed' ? 1 : 0
        );
    }
}
