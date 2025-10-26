<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class NewsSource extends Model
{
    /** @use HasFactory<\Database\Factories\NewsSourceFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'source_id',
        'name',
    ];

    public function news(): HasMany
    {
        return $this->hasMany(News::class, 'source_id');
    }
}
