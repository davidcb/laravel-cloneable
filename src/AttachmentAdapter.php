<?php

namespace Davidcb\LaravelCloneable;

use Illuminate\Database\Eloquent\Model;

interface AttachmentAdapter
{
    /**
     * Duplicate a file, identified by the reference string, which was pulled from
     * a model attribute
     * 
     * @param  \Illuminate\Database\Eloquent\Model $original
     * @param  \Illuminate\Database\Eloquent\Model $clone
     * @return string New reference to duplicated file
     */
    public function duplicate(Model $original, Model $clone);
}
