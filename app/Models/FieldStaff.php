<?php

namespace App\Models;

use Database\Factories\FieldStaffFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FieldStaff extends Model
{
    /** @use HasFactory<FieldStaffFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'field_staff';

    protected $fillable = [
        'user_id',
        'staff_code',
        'full_name',
        'phone',
        'date_of_birth',
        'certificates',
        'responsible_location_id',
        'start_date',
        'status',
        'note',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function responsibleLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'responsible_location_id');
    }

    public function responsibleLocations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'field_staff_location')
            ->withTimestamps();
    }

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'date_of_birth' => 'date',
        ];
    }
}
