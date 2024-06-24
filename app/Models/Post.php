<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Tags\HasTags;
use Spatie\Translatable\HasTranslations;

class Post extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia, HasTranslations, HasTags;

    public $translatable = ['title', 'subtitle', 'body'];


    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }

    public function author()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }


    public function registerMediaConversions(?Media $media = null): void
    {
        $t = explode('x', config('app.media.post_thumb'));

        if (config('app.media.post_thumb') == null || config('app.media.post_thumb') == '') {
            $t[0] = 500;
            $t[1] = 500;
        }

        $this->addMediaConversion('post-image')
            ->width($t[0])
            ->height($t[1])
            ->crop( $t[0], $t[1])
            ->optimize()
            ->sharpen(10)
            ->nonQueued()
            ->format('webp');

    }

    public function imgUrl()
    {
        if ($this->getMedia()->count() > 0) {
            return $this->getMedia()->first()->getUrl('post-image');
        } else {
            return asset('assets/upload/logo.svg');
        }
    }

    public function orgUrl()
    {
        if ($this->getMedia()->count() > 0) {
            return $this->getMedia()[$this->image_index]->getUrl();
        } else {
            return asset('assets/upload/logo.svg');

        }
    }


    public function spendTime()
    {
        $word = strlen(strip_tags($this->body));
        $m = ceil($word / 1350);

        return $m . ' ' . __('minute');
    }


    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function approvedComments()
    {
        return $this->morphMany(Comment::class, 'commentable')->where('status', 1);
    }

    public function mainGroup(){
        return $this->belongsTo(Group::class);
    }


//    public function toArray()
//    {
//        return [
//            'id' => $this->id,
//            'title' => $this->title,
//            'subtitle' => $this->subtitle,
//            'body' => $this->body,
//            'categories' => $this->categories->implode(' ') ?? null,
//            'author' => $this->author->name ?? null,
//            'tags' => $this->tags->implode(' ') ?? null,
//        ];
//    }
}