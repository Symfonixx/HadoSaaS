<?php

namespace Modules\Cms\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Blog extends Model
{
    use HasTranslations;

    public $translatable = ['title', 'description', 'content', 'meta_title', 'meta_description', 'meta_keywords'];

    protected $appends = ['image_link', 'meta_image_link'];

    protected $fillable = [
        'title',
        'slug',
        'category_id',
        'description',
        'content',
        'image',
        'meta_image',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'status',
        'featured',
        'visits',
    ];

    protected $casts = [
        'featured' => 'boolean',
        'visits' => 'integer',
    ];

    public function scopeFeatured($q)
    {
        $q->where('status', 'Published')->where('featured', 1);
    }

    public function scopePublished($q)
    {
        $q->where('status', 'Published');
    }

    public function getImageLinkAttribute(): string
    {
        return $this->resolveMediaPath($this->attributes['image'] ?? null);
    }

    public function getMetaImageLinkAttribute(): string
    {
        return $this->resolveMediaPath($this->attributes['meta_image'] ?? null);
    }

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    private function resolveMediaPath(?string $path): string
    {
        if ($path) {
            return asset('storage/'.$path);
        }

        return asset('images/blank.png');
    }
}
