<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'cover_image' => $this->cover_image,
            'video_url' => $this->video_url,
            'crop_tags' => $this->crop_tags,
            'region_tags' => $this->region_tags,
            'published_at' => optional($this->published_at)->toDateString(),
            'author' => $this->author?->name,
        ];
    }
}
