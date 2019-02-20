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
 * Class \Hoa\Database\Query\Where.
 *
 * Build a WHERE clause.
 */
class Where
{
    /**
     * Expressions.
     *
     * @var array
     */
    protected $_where         = [];

    /**
     * Current logic operator.
     *
     * @var ?string
     */
    protected $_logicOperator = null;



    /**
     * Add an expression (regular string or a WHERE clause).
     */
    public function where($expression): self
    {
        $where = null;

        if (!empty($this->_where)) {
            $where = ($this->_logicOperator ?: 'AND') . ' ';
        }

        if ($expression instanceof self) {
            $expression = '(' . substr((string) $expression, 7) . ')';
        }

        $this->_where[]       = $where . $expression;
        $this->_logicOperator = null;

        return $this;
    }

    /**
     * Redirect undefined calls to _calls.
     */
    public function __call(string $name, array $values): self
    {
        return call_user_func_array([$this, '_' . $name], $values);
    }

    /**
     * Set the current logic operator.
     */
    public function __get(string $name): self
    {
        switch (strtolower($name)) {
            case 'and':
            case 'or':
                $this->_logicOperator = strtoupper($name);

                break;

            default:
                return $this->$name;
        }

        return $this;
    }

    /**
     * Reset.
     */
    public function reset(): self
    {
        $this->_where = [];

        return $this;
    }

    /**
     * Generate the query.
     */
    public function __toString(): string
    {
        if (empty($this->_where)) {
            return '';
        }

        return ' WHERE ' . implode(' ', $this->_where);
    }
}
