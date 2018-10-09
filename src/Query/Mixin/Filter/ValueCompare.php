<?php

namespace Misantron\QueryBuilder\Query\Mixin\Filter;

use Misantron\QueryBuilder\Query\Condition\ValueCondition;
use Misantron\QueryBuilder\Query\Filter\Filter;
use Misantron\QueryBuilder\Query\Filter\FilterGroup;
use Misantron\QueryBuilder\Query\Mixin\Filterable;

/**
 * Trait ValueCompare.
 *
 * @property FilterGroup $filters
 */
trait ValueCompare
{
    /**
     * @param string $column
     * @param mixed  $value
     *
     * @return Filterable
     */
    public function equals(string $column, $value)
    {
        return $this->andEquals($column, $value);
    }

    /**
     * @param string $column
     * @param mixed  $value
     *
     * @return Filterable
     */
    public function andEquals(string $column, $value)
    {
        $this->filters->append(
            Filter::create(
                ValueCondition::create($column, $value, '='), 'AND'
            )
        );

        return $this;
    }

    /**
     * @param string $column
     * @param mixed  $value
     *
     * @return Filterable
     */
    public function orEquals(string $column, $value)
    {
        $this->filters->append(
            Filter::create(
                ValueCondition::create($column, $value, '='), 'OR'
            )
        );

        return $this;
    }

    /**
     * @param string $column
     * @param mixed  $value
     *
     * @return Filterable
     */
    public function notEquals(string $column, $value)
    {
        return $this->andNotEquals($column, $value);
    }

    /**
     * @param string $column
     * @param mixed  $value
     *
     * @return Filterable
     */
    public function andNotEquals(string $column, $value)
    {
        $this->filters->append(
            Filter::create(
                ValueCondition::create($column, $value, '!='), 'AND'
            )
        );

        return $this;
    }

    /**
     * @param string $column
     * @param mixed  $value
     *
     * @return Filterable
     */
    public function orNotEquals(string $column, $value)
    {
        $this->filters->append(
            Filter::create(
                ValueCondition::create($column, $value, '!='), 'OR'
            )
        );

        return $this;
    }

    /**
     * @param string $column
     * @param mixed  $value
     *
     * @return Filterable
     */
    public function more(string $column, $value)
    {
        return $this->andMore($column, $value);
    }

    /**
     * @param string $column
     * @param mixed  $value
     *
     * @return Filterable
     */
    public function andMore(string $column, $value)
    {
        $this->filters->append(
            Filter::create(
                ValueCondition::create($column, $value, '>'), 'AND'
            )
        );

        return $this;
    }

    /**
     * @param string $column
     * @param mixed  $value
     *
     * @return Filterable
     */
    public function orMore(string $column, $value)
    {
        $this->filters->append(
            Filter::create(
                ValueCondition::create($column, $value, '>'), 'OR'
            )
        );

        return $this;
    }

    /**
     * @param string $column
     * @param mixed  $value
     *
     * @return Filterable
     */
    public function moreOrEquals(string $column, $value)
    {
        return $this->andMoreOrEquals($column, $value);
    }

    /**
     * @param string $column
     * @param mixed  $value
     *
     * @return Filterable
     */
    public function andMoreOrEquals(string $column, $value)
    {
        $this->filters->append(
            Filter::create(
                ValueCondition::create($column, $value, '>='), 'AND'
            )
        );

        return $this;
    }

    /**
     * @param string $column
     * @param mixed  $value
     *
     * @return Filterable
     */
    public function orMoreOrEquals(string $column, $value)
    {
        $this->filters->append(
            Filter::create(
                ValueCondition::create($column, $value, '>='), 'OR'
            )
        );

        return $this;
    }

    /**
     * @param string $column
     * @param mixed  $value
     *
     * @return Filterable
     */
    public function less(string $column, $value)
    {
        return $this->andLess($column, $value);
    }

    /**
     * @param string $column
     * @param mixed  $value
     *
     * @return Filterable
     */
    public function andLess(string $column, $value)
    {
        $this->filters->append(
            Filter::create(
                ValueCondition::create($column, $value, '<'), 'AND'
            )
        );

        return $this;
    }

    /**
     * @param string $column
     * @param mixed  $value
     *
     * @return Filterable
     */
    public function orLess(string $column, $value)
    {
        $this->filters->append(
            Filter::create(
                ValueCondition::create($column, $value, '<'), 'OR'
            )
        );

        return $this;
    }

    /**
     * @param string $column
     * @param mixed  $value
     *
     * @return Filterable
     */
    public function lessOrEquals(string $column, $value)
    {
        return $this->andLessOrEquals($column, $value);
    }

    /**
     * @param string $column
     * @param mixed  $value
     *
     * @return Filterable
     */
    public function andLessOrEquals(string $column, $value)
    {
        $this->filters->append(
            Filter::create(
                ValueCondition::create($column, $value, '<='), 'AND'
            )
        );

        return $this;
    }

    /**
     * @param string $column
     * @param mixed  $value
     *
     * @return Filterable
     */
    public function orLessOrEquals(string $column, $value)
    {
        $this->filters->append(
            Filter::create(
                ValueCondition::create($column, $value, '<='), 'OR'
            )
        );

        return $this;
    }
}
