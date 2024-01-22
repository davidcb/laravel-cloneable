<?php

namespace Davidcb\LaravelCloneable\Adapters;

use Illuminate\Database\Eloquent\Model;
use Davidcb\LaravelCloneable\AttachmentAdapter;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class LaravelMediaLibrary implements AttachmentAdapter
{
    public function duplicate(Model $original, Model $clone): Model
    {
        $attributes = $clone->getCloneableFileAttributes();

        if ($attributes === []) {
            return $clone;
        }

        if ($attributes === ['*']) {
            $attributes = $original->media->pluck('collection_name')->unique()->toArray();
        }

        $original->media->each(function (Media $media) use ($clone, $attributes) {
            if (!in_array($media->collection_name, $attributes)) {
                return;
            }

            $clone->addMedia($media->getPath())
                ->preservingOriginal()
                ->withProperties($media->toArray())
                ->toCollection($media->collection_name);
        });

        return $clone;
    }
}
