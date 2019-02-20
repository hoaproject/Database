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

namespace Hoa\Database\IDal;

/**
 * Interface \Hoa\Database\IDal\Wrapper.
 *
 * Interface of a DAL wrapper.
 */
interface Wrapper
{
    /**
     * Create a DAL instance, representing a connection to a database.
     */
    public function __construct(
        string $dsn,
        string $username,
        string $password,
        array $driverOptions = []
    );

    /**
     * Initiate a transaction.
     */
    public function beginTransaction(): bool;

    /**
     * Commit a transaction.
     */
    public function commit(): bool;

    /**
     * Roll back a transaction.
     */
    public function rollBack(): bool;

    /**
     * Return the ID of the last inserted row or sequence value.
     */
    public function lastInsertId(string $name = null): string;

    /**
     * Prepare a statement for execution and returns a statement object.
     */
    public function prepare(string $statement, array $options = []): WrapperStatement;

    /**
     * Quote a string for use in a query.
     */
    public function quote(?string $string, int $type = -1): string;

    /**
     * Execute an SQL statement, returning a result set as a
     * \Hoa\Database\IDal\WrapperStatement object.
     */
    public function query(string $statement): WrapperStatement;

    /**
     * Fetch the SQLSTATE associated with the last operation on the database
     * handle.
     */
    public function errorCode(): string;

    /**
     * Fetch extends error information associated with the last operation on the
     * database handle.
     */
    public function errorInfo(): array;

    /**
     * Return an array of available drivers.
     */
    public function getAvailableDrivers(): array;

    /**
     * Set attributes.
     */
    public function setAttributes(array $attributes): array;

    /**
     * Set a specific attribute.
     */
    public function setAttribute($attribute, $value);

    /**
     * Retrieve all database connection attributes.
     */
    public function getAttributes(): array;

    /**
     * Retrieve a database connection attribute.
     */
    public function getAttribute(string $attribute);
}
