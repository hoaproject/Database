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

namespace Hoa\Database\Layer\Pdo;

use Hoa\Database;

/**
 * Class \Hoa\Database\Layer\Pdo\Statement.
 *
 * Wrap PDOStatement.
 */
class Statement implements Database\IDal\WrapperStatement
{
    /**
     * The statement instance.
     *
     * @var \PDOStatement
     */
    protected $_statement = null;

    /**
     * The fetching style options.
     *
     * @var array
     */
    protected $_style     = [
        Database\DalStatement::STYLE_OFFSET      => Database\DalStatement::FROM_START,
        Database\DalStatement::STYLE_ORIENTATION => Database\DalStatement::FORWARD,
        Database\DalStatement::STYLE_MODE        => Database\DalStatement::AS_MAP
    ];



    /**
     * Create a statement instance.
     */
    public function __construct(\PDOStatement $statement)
    {
        $this->setStatement($statement);

        return;
    }

    /**
     * Set the statement instance.
     */
    protected function setStatement(\PDOStatement $statement): \PDOStatement
    {
        $old              = $this->_statement;
        $this->_statement = $statement;

        return $old;
    }

    /**
     * Get the statement instance.
     */
    protected function getStatement(): \PDOStatement
    {
        return $this->_statement;
    }

    /**
     * Execute a prepared statement.
     */
    public function execute(array $bindParameters = null): Database\IDal\WrapperStatement
    {
        if (false === $this->getStatement()->execute($bindParameters)) {
            throw new Database\Exception(
                '%3$s (%1$s/%2$d)',
                0,
                $this->errorInfo()
            );
        }

        return $this;
    }

    /**
     * Bind a parameter to te specified variable name.
     */
    public function bindParameter(
        $parameter,
        &$value,
        ?int $type  = null,
        int $length = null
    ): bool {
        if (null === $type) {
            return $this->getStatement()->bindParam($parameter, $value);
        }

        if (null === $length) {
            return $this->getStatement()->bindParam($parameter, $value, $type);
        }

        return $this->getStatement()->bindParam($parameter, $value, $type, $length);
    }

    /**
     * Set the iterator fetching style.
     */
    public function setFetchingStyle(
        int $offset      = Database\DalStatement::FROM_START,
        int $orientation = Database\DalStatement::FORWARD,
        int $style       = Database\DalStatement::AS_MAP,
        $arg1            = null,
        array $arg2      = null
    ): Database\IDal\WrapperStatement {
        $this->_style[Database\DalStatement::STYLE_OFFSET]      = $offset;
        $this->_style[Database\DalStatement::STYLE_ORIENTATION] = $orientation;
        $this->_style[Database\DalStatement::STYLE_MODE]        = $style;

        if (Database\DalStatement::AS_CLASS === $style) {
            $this->_style[Database\DalStatement::STYLE_CLASS_NAME]            = $arg1;
            $this->_style[Database\DalStatement::STYLE_CONSTRUCTOR_ARGUMENTS] = $arg2;
        } elseif (Database\DalStatement::AS_REUSABLE_OBJECT === $style) {
            $this->_style[Database\DalStatement::STYLE_OBJECT] = $arg1;
        }

        return $this;
    }

    /**
     * Get an Iterator.
     */
    public function getIterator(): Iterator
    {
        return new Iterator(
            $this->getStatement(),
            $this->_style
        );
    }

    /**
     * Return an array containing all of the result set rows.
     */
    public function fetchAll(): array
    {
        return $this->getStatement()->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Fetch the first row in the result set.
     */
    public function fetchFirst(int $style = null)
    {
        return $this->fetch($style, \PDO::FETCH_ORI_FIRST);
    }

    /**
     * Fetch the last row in the result set.
     */
    public function fetchLast(int $style = null)
    {
        return $this->fetch($style, \PDO::FETCH_ORI_LAST);
    }

    /**
     * Fetch a row in the result set.
     */
    protected function fetch(int $style, int $orientation)
    {
        return $this->getStatement()->fetch(
            $style ?: $this->_style,
            $orientation
        );
    }

    /**
     * Return a single column from the next row of the result set or false if
     * there is no more row.
     */
    public function fetchColumn(int $column = 0)
    {
        return $this->getStatement()->fetchColumn($column);
    }

    /**
     * Close the cursor, enabling the statement to be executed again.
     */
    public function closeCursor(): bool
    {
        return $this->getStatement()->closeCursor();
    }

    /**
     * Fetch the SQLSTATE associated with the last operation on the statement
     * handle.
     */
    public function errorCode(): string
    {
        return $this->getStatement()->errorCode();
    }

    /**
     * Fetch extends error information associated with the last operation on the
     * statement handle.
     */
    public function errorInfo(): array
    {
        return $this->getStatement()->errorInfo();
    }
}
