<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class HelpRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'reason',
        'description',
        'amount_requested',
        'document_path',
        'status',
    ];

    protected $casts = [
        'amount_requested' => 'decimal:2',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function validations(): MorphMany
    {
        return $this->morphMany(Validation::class, 'valuable');
    }
}
