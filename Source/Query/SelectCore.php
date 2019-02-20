<?php

declare(strict_types=1);

/**
 * Hoa
 *
 *
 * @license
 *
 * New BSD License
 *
 * Copyright © 2007-2017, Hoa community. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the Hoa nor the names of its contributors may be
 *       used to endorse or promote products derived from this software without
 *       specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDERS AND CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace Hoa\Database\Query;

use Hoa\Database\Exception;

/**
 * Class \Hoa\Database\Query\SelectCore.
 *
 * Core of the SELECT query.
 */
abstract class SelectCore extends Where
{
    use EncloseIdentifier;

    /**
     * Columns.
     *
     * @var array
     */
    protected $_columns       = [];

    /**
     * SELECT DISTINCT or SELECT ALL.
     *
     * @var ?string
     */
    protected $_distinctOrAll = null;

    /**
     * Sources.
     *
     * @var array
     */
    protected $_from          = [];

    /**
     * Group by expressions.
     *
     * @var array
     */
    protected $_groupBy       = [];

    /**
     * Having expression.
     *
     * @var ?string
     */
    protected $_having        = null;



    /**
     * Set columns.
     */
    public function __construct(array $columns = [])
    {
        $this->_columns = $columns;

        return;
    }

    /**
     * Make a SELECT DISTINCT.
     */
    public function distinct(): self
    {
        $this->_distinctOrAll = 'DISTINCT';

        return $this;
    }

    /**
     * Make a SELECT ALL.
     */
    public function all(): self
    {
        $this->_distinctOrAll = 'ALL';

        return $this;
    }

    /**
     * Select a column.
     */
    public function select(string ...$columns): self
    {
        foreach ($columns as $column) {
            $this->_columns[] = $column;
        }

        return $this;
    }

    /**
     * Group by expression.
     */
    public function groupBy(string ...$expressions): self
    {
        foreach ($expressions as $expression) {
            $this->_groupBy[] = $expression;
        }

        return $this;
    }

    /**
     * Having expression.
     */
    public function having(string $expression): self
    {
        $this->_having = $expression;

        return $this;
    }

    /**
     * Set source (regular or a SELECT query).
     */
    public function from(...$sources): self
    {
        foreach ($sources as $source) {
            if ($source instanceof self) {
                $source = '(' . $source . ')';
            }

            $this->_from[] = $source;
        }

        return $this;
    }

    /**
     * Alias the last declared source.
     */
    public function _as(string $alias): self
    {
        if (empty($this->_from)) {
            return $this;
        }

        $this->_from[$alias] = array_pop($this->_from);

        return $this;
    }

    /**
     * Join a source (regular of a SELECT query).
     */
    public function join($source): Join
    {
        return $this->_join('JOIN', $source);
    }

    /**
     * Natural join a source (regular of a SELECT query).
     */
    public function naturalJoin($source): Join
    {
        return $this->_join('NATURAL JOIN', $source);
    }

    /**
     * Left join a source (regular of a SELECT query).
     */
    public function leftJoin($source): Join
    {
        return $this->_join('LEFT JOIN', $source);
    }

    /**
     * Natural left join a source (regular of a SELECT query).
     */
    public function naturalLeftJoin($source): Join
    {
        return $this->_join('NATURAL LEFT JOIN', $source);
    }

    /**
     * Left outer join a source (regular of a SELECT query).
     */
    public function leftOuterJoin($source): Join
    {
        return $this->_join('LEFT OUTER JOIN', $source);
    }

    /**
     * Natural left outer join a source (regular of a SELECT query).
     */
    public function naturalLeftOuterJoin($source): Join
    {
        return $this->_join('NATURAL LEFT OUTER JOIN', $source);
    }

    /**
     * Inner join a source (regular of a SELECT query).
     */
    public function innerJoin($source): Join
    {
        return $this->_join('INNER JOIN', $source);
    }

    /**
     * Natural inner join a source (regular of a SELECT query).
     */
    public function naturalInnerJoin($source): Join
    {
        return $this->_join('NATURAL INNER JOIN', $source);
    }

    /**
     * Cross join a source (regular of a SELECT query).
     */
    public function crossJoin($source): Join
    {
        return $this->_join('CROSS JOIN', $source);
    }

    /**
     * Natural cross join a source (regular of a SELECT query).
     */
    public function naturalCrossJoin($source): Join
    {
        return $this->_join('NATURAL CROSS JOIN', $source);
    }

    /**
     * Make a join.
     */
    protected function _join(string $type, $source): Join
    {
        if (empty($this->_from)) {
            throw new Exception('Cannot join if there is no `FROM` set.', 0);
        }

        if ($source instanceof self) {
            $source = '(' . $source . ')';
        }

        end($this->_from);
        $key               = key($this->_from);
        $value             = current($this->_from);
        $this->_from[$key] =
            $this->enclose($value) . ' ' .
            $type . ' ' .
            $this->enclose($source);

        return new Join($this, $this->_from);
    }

    /**
     * Reset some properties.
     */
    public function reset(): parent
    {
        parent::reset();
        $this->_columns       = [];
        $this->_distinctOrAll = null;
        $this->_groupBy       = [];
        $this->_having        = [];
        $this->_from          = [];

        return $this;
    }

    /**
     * Generate the query.
     */
    public function __toString(): string
    {
        $out = 'SELECT';

        if (null !== $this->_distinctOrAll) {
            $out .= ' ' . $this->_distinctOrAll;
        }

        if (!empty($this->_columns)) {
            $out .= ' ' . implode(', ', $this->enclose($this->_columns));
        } else {
            $out .= ' *';
        }

        if (!empty($this->_from)) {
            $out    .= ' FROM ';
            $handle  = [];

            foreach ($this->_from as $alias => $from) {
                if (is_int($alias)) {
                    $handle[] = $this->enclose($from);
                } else {
                    $handle[] =
                        $this->enclose($from) .
                        ' AS ' . $this->enclose($alias);
                }
            }

            $out .= implode(', ', $handle);
        }

        $out .= parent::__toString();

        if (!empty($this->_groupBy)) {
            $out .=
                ' GROUP BY ' .
                implode(', ', $this->enclose($this->_groupBy));

            if (!empty($this->_having)) {
                $out .= ' HAVING ' . $this->_having;
            }
        }

        return $out;
    }
}
