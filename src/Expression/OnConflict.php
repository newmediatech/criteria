<?php

declare(strict_types=1);

namespace Misantron\QueryBuilder\Expression;

use Misantron\QueryBuilder\Compilable;
use Misantron\QueryBuilder\Query\Update;

final class OnConflict implements Compilable
{
    /**
     * @var ConflictTarget
     */
    private $target;

    /**
     * @var Update|ActionNothing
     */
    private $action;

    public function __construct(ConflictTarget $target, ?Update $action = null)
    {
        $this->target = $target;
        $this->action = $action instanceof Update ? $action : new ActionNothing();
    }

    public function compile(): string
    {
        return sprintf(' ON CONFLICT %s DO %s', $this->target->compile(), $this->action->compile());
    }
}
