<?php

namespace Davidcb\LaravelCloneable;

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;
use Davidcb\LaravelCloneable\AttachmentAdapter;
use Illuminate\Contracts\Events\Dispatcher as Events;

class Cloner
{
    private $attachment;
    private $events;

    public function __construct(
        AttachmentAdapter $attachment = null,
        Events $events = null
    ) {
        $this->attachment = $attachment;
        $this->events = $events;
    }

    public function duplicate(Model $model, mixed $relation = null): Model
    {
        $clone = $this->cloneModel($model);

        $this->dispatchOnCloningEvent($clone, $relation, $model);

        if ($relation) {
            if (!is_a($relation, 'Illuminate\Database\Eloquent\Relations\BelongsTo')) {
                $relation->save($clone);
            }
        } else {
            $clone->save();
        }

        $this->duplicateAttachments($model, $clone);
        $clone->save();

        $this->cloneRelations($model, $clone);

        $this->dispatchOnClonedEvent($clone, $model);

        return $clone;
    }

    protected function cloneModel(Model $model): Model
    {
        $exempt = method_exists($model, 'getCloneExemptAttributes') ?
            $model->getCloneExemptAttributes() : null;
        $clone = $model->replicate($exempt);

        return $clone;
    }

    protected function duplicateAttachments(Model $model, Model $clone): void
    {
        if (!$this->attachment || !method_exists($clone, 'getCloneableFileAttributes')) {
            return;
        }

        $this->attachment->duplicate($model, $clone);
    }

    protected function dispatchOnCloningEvent(Model $clone, mixed $relation = null, ?Object $src = null, bool $child = false): void
    {
        // Set the child flag
        if ($relation) {
            $child = true;
        }

        // Notify listeners via callback or event
        if (method_exists($clone, 'onCloning')) {
            $clone->onCloning($src, $child);
        }

        $this->events->dispatch('cloner::cloning: ' . get_class($src), [$clone, $src]);
    }

    protected function dispatchOnClonedEvent(Model $clone, ?Object $src): void
    {
        // Notify listeners via callback or event
        if (method_exists($clone, 'onCloned')) {
            $clone->onCloned($src);
        }

        $this->events->dispatch('cloner::cloned: ' . get_class($src), [$clone, $src]);
    }

    protected function cloneRelations(Model $model, Model $clone): void
    {
        if (!method_exists($model, 'getCloneableRelations')) {
            return;
        }

        foreach ($model->getCloneableRelations() as $relationName) {
            $this->duplicateRelation($model, $relationName, $clone);
        }
    }

    protected function duplicateRelation(Model $model, string $relation_name, Model $clone): void
    {
        $relation = call_user_func([$model, $relation_name]);
        if (is_a($relation, 'Illuminate\Database\Eloquent\Relations\BelongsToMany')) {
            $this->duplicatePivotedRelation($relation, $relation_name, $clone);
        } else {
            $this->duplicateDirectRelation($relation, $relation_name, $clone);
        }
    }

    protected function duplicatePivotedRelation(Object $relation, string $relation_name, Model $clone): void
    {
        // Loop trough current relations and attach to clone
        $relation->as('pivot')->get()->each(function ($foreign) use ($clone, $relation_name) {
            $pivot_attributes = Arr::except($foreign->pivot->getAttributes(), [
                $foreign->pivot->getKeyName(),
                $foreign->pivot->getRelatedKey(),
                $foreign->pivot->getForeignKey(),
                $foreign->pivot->getCreatedAtColumn(),
                $foreign->pivot->getUpdatedAtColumn()
            ]);

            foreach (array_keys($pivot_attributes) as $attributeKey) {
                $pivot_attributes[$attributeKey] = $foreign->pivot->getAttribute($attributeKey);
            }

            if ($foreign->pivot->incrementing) {
                unset($pivot_attributes[$foreign->pivot->getKeyName()]);
            }

            $clone->$relation_name()->attach($foreign, $pivot_attributes);
        });
    }

    protected function duplicateDirectRelation(Object $relation, string $relation_name, Model $clone): void
    {
        $relation->get()->each(function ($foreign) use ($clone, $relation_name) {
            $cloned_relation = $this->duplicate($foreign, $clone->$relation_name());

            if (is_a($clone->$relation_name(), 'Illuminate\Database\Eloquent\Relations\BelongsTo')) {
                $clone->$relation_name()->associate($cloned_relation);
                $clone->save();
            }
        });
    }
}
