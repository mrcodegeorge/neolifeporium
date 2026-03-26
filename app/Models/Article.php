<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'author_id',
        'title',
        'slug',
        'excerpt',
        'body',
        'cover_image',
        'video_url',
        'meta_title',
        'meta_description',
        'crop_tags',
        'region_tags',
        'published_at',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'crop_tags' => 'array',
            'region_tags' => 'array',
            'published_at' => 'datetime',
            'is_published' => 'boolean',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function views(): HasMany
    {
        return $this->hasMany(ArticleView::class);
    }

    public function recommendedProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'article_product')->withTimestamps();
    }
}
