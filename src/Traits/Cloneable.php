<?php

namespace Davidcb\LaravelCloneable\Traits;

use Davidcb\LaravelCloneable\Cloner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

trait Cloneable
{
    public function getCloneableExemptAttributes(): array
    {
        // Default exempt columns (id, created_at, updated_at)
        $defaults = [
            $this->getKeyName(),
            $this->getCreatedAtColumn(),
            $this->getUpdatedAtColumn(),
        ];

        // If no exempt attributes are specified, return the defaults
        if (!isset($this->clone_exempt_attributes)) {
            return $defaults;
        }

        // Return the defaults merged with the specified exempt attributes
        return array_merge($defaults, $this->clone_exempt_attributes);
    }

    public function getCloneableFileAttributes(): array
    {
        return $this->cloneable_file_attributes ?? [];
    }

    public function getCloneableRelations(): array
    {
        return $this->cloneable_relations ?? [];
    }

    public function addCloneableRelation(string $relation): void
    {
        $relations = $this->getCloneableRelations();

        if (in_array($relation, $relations)) {
            return;
        }

        $relations[] = $relation;
        $this->cloneable_relations = $relations;
    }

    public function duplicate(): Model
    {
        return App::make(Cloner::class)->duplicate($this);
    }

    public function onCloning(Object $src, bool $child = false): void
    {
    }

    public function onCloned(Object $src): void
    {
    }
}
