<?php

namespace Davidcb\LaravelCloneable\Test;

use Illuminate\Database\Eloquent\Model;

class DummyRelated extends Model
{

    protected $table = 'dummies_related';
    protected $guarded = [];

    protected $fillable = ['title', 'description', 'dummy_id'];

    public function dummy()
    {
        return $this->belongsTo(Dummy::class);
    }
}
