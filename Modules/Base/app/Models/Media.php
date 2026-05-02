<?php

namespace Modules\Base\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Media extends Model
{
    protected $table = 'media';

    protected $fillable = [
        'name',
        'path',
        'disk',
        'mime_type',
        'size',
        'user_id',
    ];

    protected $appends = ['url'];

    public function getUrlAttribute(): string
    {
        return asset('storage/'.$this->path);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
