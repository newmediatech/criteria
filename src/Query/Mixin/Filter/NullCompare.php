<?php

namespace MediaTech\Query\Query\Mixin\Filter;


use MediaTech\Query\Query\Condition\NullCondition;
use MediaTech\Query\Query\Filter\Filter;
use MediaTech\Query\Query\Filter\FilterGroup;
use MediaTech\Query\Query\Mixin\Filterable;

/**
 * Trait NullCompare
 * @package MediaTech\Query\Query\Mixin\Filter
 *
 * @property FilterGroup $filters
 */
trait NullCompare
{
    /**
     * @param string $column
     *
     * @return Filterable
     */
    public function isNull(string $column)
    {
        return $this->andIsNull($column);
    }

    /**
     * @param string $column
     *
     * @return Filterable
     */
    public function andIsNull(string $column)
    {
        $this->filters->append(
            Filter::create(
                NullCondition::create($column, 'IS'), 'AND'
            )
        );
        return $this;
    }

    /**
     * @param string $column
     *
     * @return Filterable
     */
    public function orIsNull(string $column)
    {
        $this->filters->append(
            Filter::create(
                NullCondition::create($column, 'IS'), 'OR'
            )
        );
        return $this;
    }

    /**
     * @param string $column
     *
     * @return Filterable
     */
    public function isNotNull(string $column)
    {
        return $this->andIsNotNull($column);
    }

    /**
     * @param string $column
     *
     * @return Filterable
     */
    public function andIsNotNull(string $column)
    {
        $this->filters->append(
            Filter::create(
                NullCondition::create($column, 'IS NOT'), 'AND'
            )
        );
        return $this;
    }

    /**
     * @param string $column
     *
     * @return Filterable
     */
    public function orIsNotNull(string $column)
    {
        $this->filters->append(
            Filter::create(
                NullCondition::create($column, 'IS NOT'), 'OR'
            )
        );
        return $this;
    }
}