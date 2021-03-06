<?php

namespace Cristal\ApiWrapper\Bridges\Laravel\Relations;

use Cristal\ApiWrapper\Model;
use Cristal\ApiWrapper\Relations\BelongsTo as CoreBelongsTo;
use Illuminate\Database\Eloquent\Model as ModelEloquent;
use LogicException;

class BelongsTo extends CoreBelongsTo
{
    /**
     * {@inheritDoc}
     */
    public function __construct($parent, Model $related, $foreignKey, $localKey)
    {
        if (!($parent instanceof Model || $parent instanceof ModelEloquent)) {
            throw new LogicException('Parent must be a Model instance.');
        }

        $this->parent = $parent;
        $this->related = $related;
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;

        $this->addConstraints();
    }
}
