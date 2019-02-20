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

/**
 * Class \Hoa\Database\Query\Insert.
 *
 * Build an INSERT query.
 */
class Insert implements Dml
{
    use EncloseIdentifier;

    /**
     * Source.
     *
     * @var ?string
     */
    protected $_into          = null;

    /**
     * Alternative to INSERT.
     *
     * @var ?string
     */
    protected $_or            = null;

    /**
     * Columns.
    *
     * @var array
     */
    protected $_columns       = [];

    /**
     * Values (tuples).
     *
     * @var mixed
     */
    protected $_values        = null;

    /**
     * Whether we should use default values or not.
     *
     * @var bool
     */
    protected $_defaultValues = false;



    /**
     * Set source.
     */
    public function into(string $name): self
    {
        $this->_into = $name;

        return $this;
    }

    /**
     * Insert or rollback.
     */
    public function rollback(): self
    {
        return $this->_or('ROLLBACK');
    }

    /**
     * Insert or abort.
     */
    public function abort(): self
    {
        return $this->_or('ABORT');
    }

    /**
     * Insert or replace.
     */
    public function replace(): self
    {
        return $this->_or('REPLACE');
    }

    /**
     * Insert or fail.
     */
    public function fail(): self
    {
        return $this->_or('FAIL');
    }

    /**
     * Insert or ignore.
     */
    public function ignore(): self
    {
        return $this->_or('IGNORE');
    }

    /**
     * Declare an alternative to “INSERT”.
     */
    protected function _or(string $or): self
    {
        $this->_or = $or;

        return $this;
    }

    /**
     * Set columns.
     */
    public function on(string ...$columns): self
    {
        foreach ($columns as $column) {
            $this->_columns[] = $column;
        }

        return $this;
    }

    /**
     * Set values (one call per tuple).
     * Expression can be: a regular value or a SELECT query.
     */
    public function values(...$expressions): self
    {
        if (1 === count($expressions) && $expressions[0] instanceof Select) {
            $this->_values = (string) $expressions[0];
        } else {
            if (is_string($this->_values)) {
                $this->_values = [];
            }

            $values = &$this->_values[];
            $values = [];

            foreach ($expressions as $expression) {
                $values[] = $expression;
            }
        }

        return $this;
    }

    /**
     * Use default values.
     */
    public function defaultValues(): self
    {
        $this->_defaultValues = true;

        return $this;
    }

    /**
     * Allow to use the “or” attribute to chain method calls.
     */
    public function __get(string $name)
    {
        switch (strtolower($name)) {
            case 'or':
                return $this;

            default:
                return $this->$name;
        }
    }

    /**
     * Generate the query.
     */
    public function __toString(): string
    {
        $out = 'INSERT';

        if (null !== $this->_or) {
            $out .= ' OR ' . $this->_or;
        }

        $out .= ' INTO ' . $this->enclose($this->_into);

        if (true === $this->_defaultValues) {
            return $out . ' DEFAULT VALUES';
        }

        if (!empty($this->_columns)) {
            $out .= ' (' . implode(', ', $this->enclose($this->_columns)) . ')';
        }

        if (is_string($this->_values)) {
            return $out . ' ' . $this->_values;
        }

        $tuples = [];

        foreach ($this->_values as $tuple) {
            $tuples[] = '(' . implode(', ', $tuple) . ')';
        }

        return $out . ' VALUES ' . implode(', ', $tuples);
    }
}
