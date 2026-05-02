<?php

namespace Modules\Cms\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Faq extends Model
{
    use HasTranslations;

    public $translatable = ['question', 'answer'];

    protected $fillable = [
        'question',
        'answer',
        'rank',
        'status',
    ];

    protected $casts = [
        'rank' => 'integer',
    ];

    public function scopePublished($query)
    {
        return $query->where('status', 'Published');
    }
}
