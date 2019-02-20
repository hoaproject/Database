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

use Hoa\Consistency;

/**
 * Class \Hoa\Database\Query.
 *
 * Multiton of queries.
 */
class Query
{
    /**
     * Multiton of queries.
     *
     * @var array
     */
    protected static $_queries = [];

    /**
     * Current instance ID.
     *
     * @var ?string
     */
    protected $_id             = null;



    /**
     * Set current instance ID.
     */
    public function setId(string $id): self
    {
        $this->_id = $id;

        return $this;
    }

    /**
     * Get current instance ID.
     */
    public function getId(): ?string
    {
        return $this->_id;
    }

    /**
     * Start a START query.
     */
    public function select(string ...$columns): Select
    {
        return $this->store(new Select($columns));
    }

    /**
     * Start an INSERT query.
     */
    public function insert(): Insert
    {
        return $this->store(new Insert());
    }

    /**
     * Start an UPDATE query.
     */
    public function update(): Update
    {
        return $this->store(new Update());
    }

    /**
     * Start a DELETE query.
     */
    public function delete(): Delete
    {
        return $this->store(new Delete());
    }

    /**
     * Start a WHERE clause.
     */
    public function where(string $expression): Where
    {
        $where = new Where();

        return $this->store($where->where($expression));
    }

    /**
     * Store the current instance if necessary.
     */
    protected function store($object)
    {
        if (null === $id = $this->getId()) {
            $out = $object;
        } else {
            $out = static::$_queries[$id] = $object;
        }

        $this->_id = null;

        return $out;
    }

    /**
     * Get a query (a clone of it).
     */
    public static function get(string $id): ?Dml
    {
        if (null === $out = static::getReference($id)) {
            return null;
        }

        return clone $out;
    }

    /**
     * Get a query (not a clone of it).
     */
    public static function getReference(string $id): ?Dml
    {
        if (false === array_key_exists($id, static::$_queries)) {
            return null;
        }

        return static::$_queries[$id];
    }
}

/**
 * Flex entity.
 */
Consistency::flexEntity(Query::class);
