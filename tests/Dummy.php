<?php

namespace Davidcb\LaravelCloneable\Test;

use Illuminate\Database\Eloquent\Model;
use Davidcb\LaravelCloneable\Traits\Cloneable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Dummy extends Model implements HasMedia
{
    use Cloneable;
    use InteractsWithMedia;

    protected $table = 'dummies';
    protected $guarded = [];

    protected $fillable = ['title', 'description'];

    protected $cloneable_relations = [];
    protected $cloneable_file_attributes = ['*'];

    public function related()
    {
        return $this->hasMany(DummyRelated::class);
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('image')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png']);
    }
}
