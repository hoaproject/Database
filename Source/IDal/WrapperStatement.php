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

use Hoa\Database;
use Hoa\Iterator;

/**
 * Interface \Hoa\Database\IDal\WrapperStatement.
 *
 * Interface of a DAL statement wrapper.
 */
interface WrapperStatement extends Iterator\Aggregate
{
    /**
     * Execute a prepared statement.
     */
    public function execute(array $bindParameters = []): WrapperStatement;

    /**
     * Bind a parameter to te specified variable name.
     */
    public function bindParameter(
        $parameter,
        &$value,
        ?int $type   = null,
        int  $length = null
    ): bool;

    /**
     * Return an array containing all of the result set rows.
     */
    public function fetchAll(): array;

    /**
     * Set the Iterator fetching style.
     */
    public function setFetchingStyle(
        int $offset      = Database\DalStatement::FROM_START,
        int $orientation = Database\DalStatement::FORWARD,
        int $style       = Database\DalStatement::AS_MAP,
        $arg1            = null,
        array $arg2      = null
    ) : WrapperStatement;

    /**
     * Fetch the first row in the result set.
     */
    public function fetchFirst(int $style = null);

    /**
     * Fetch the last row in the result set.
     */
    public function fetchLast(int $style = null);

    /**
     * Return a single column from the next row of the result set or false if
     * there is no more row.
     */
    public function fetchColumn(int $column = 0);

    /**
     * Close the cursor, enabling the statement to be executed again.
     */
    public function closeCursor(): bool;

    /**
     * Fetch the SQLSTATE associated with the last operation on the statement
     * handle.
     */
    public function errorCode(): string;

    /**
     * Fetch extends error information associated with the last operation on the
     * statement handle.
     */
    public function errorInfo(): array;
}
