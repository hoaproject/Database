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

use Hoa\Consistency;
use Hoa\Database;

/**
 * Class \Hoa\Database\Layer\Pdo.
 *
 * Wrap PDO.
 */
class Pdo implements Database\IDal\Wrapper
{
    /**
     * Connection to database.
     *
     * @var \PDO
     */
    protected $_connection = null;



    /**
     * Create a DAL instance, representing a connection to a database.
     */
    public function __construct(
        string $dsn,
        string $username,
        string $password,
        array $driverOptions = []
    ) {
        if (false === extension_loaded('pdo')) {
            throw new Database\Exception(
                'The module PDO is not enabled.',
                0
            );
        }

        $connection = null;

        try {
            $connection = new \PDO($dsn, $username, $password, $driverOptions);
        } catch (\PDOException $e) {
            throw new Database\Exception(
                $e->getMessage(),
                $e->getCode(),
                null,
                $e
            );
        }

        $this->setConnection($connection);

        return;
    }

    /**
     * Set the connection.
     */
    protected function setConnection(\PDO $connection): \PDO
    {
        $old               = $this->_connection;
        $this->_connection = $connection;

        return $old;
    }

    /**
     * Get the connection instance.
     */
    protected function getConnection(): \PDO
    {
        if (null === $this->_connection) {
            throw new Database\Exception(
                'Cannot return a null connection.',
                1
            );
        }

        return $this->_connection;
    }

    /**
     * Initiate a transaction.
     */
    public function beginTransaction(): bool
    {
        return $this->getConnection()->beginTransaction();
    }

    /**
     * Commit a transaction.
     */
    public function commit(): bool
    {
        return $this->getConnection()->commit();
    }

    /**
     * Roll back a transaction.
     */
    public function rollBack(): bool
    {
        return $this->getConnection()->rollBack();
    }

    /**
     * Return the ID of the last inserted row or sequence value.
     */
    public function lastInsertId(string $name = null): string
    {
        if (null === $name) {
            return $this->getConnection()->lastInsertId();
        }

        return $this->getConnection()->lastInsertId($name);
    }

    /**
     * Prepare a statement for execution and returns a statement object.
     */
    public function prepare(string $statement, array $options = []): Database\IDal\WrapperStatement
    {
        if (!isset($options[\PDO::ATTR_CURSOR])) {
            try {
                $this->getConnection()->getAttribute(\PDO::ATTR_CURSOR);
                $options[\PDO::ATTR_CURSOR] = \PDO::CURSOR_SCROLL;
            } catch (\PDOException $e) {
                // Cursors are not supported by the driver, see
                // https://github.com/hoaproject/Database/issues/35.
            }
        }

        $handle = $this->getConnection()->prepare($statement, $options);

        if (!($handle instanceof \PDOStatement)) {
            throw new Database\Exception(
                '%3$s (%1$s/%2$d).',
                2,
                $this->errorInfo()
            );
        }

        return new Statement($handle);
    }

    /**
     * Quote a sting for use in a query.
     */
    public function quote(?string $string, int $type = -1): string
    {
        if ($type < 0) {
            return $this->getConnection()->quote($string);
        }

        return $this->getConnection()->quote($string, $type);
    }

    /**
     * Execute an SQL statement, returning a result set as a
     * \Hoa\Database\Layer\Pdo\Statement object.
     */
    public function query(string $statement): Database\IDal\WrapperStatement
    {
        $handle = $this->getConnection()->query($statement);

        if (!($handle instanceof \PDOStatement)) {
            throw new Database\Exception(
                '%3$s (%1$s/%2$d).',
                3,
                $this->errorInfo()
            );
        }

        return new Statement($handle);
    }

    /**
     * Fetch the SQLSTATE associated with the last operation on the database
     * handle.
     */
    public function errorCode(): string
    {
        return $this->getConnection()->errorCode();
    }

    /**
     * Fetch extends error information associated with the last operation on the
     * database handle.
     */
    public function errorInfo(): array
    {
        return $this->getConnection()->errorInfo();
    }

    /**
     * Return an array of available drivers.
     */
    public function getAvailableDrivers(): array
    {
        return $this->getConnection()->getAvailableDrivers();
    }

    /**
     * Set attributes.
     */
    public function setAttributes(array $attributes): array
    {
        $out = true;

        foreach ($attributes as $attribute => $value) {
            $out &= $this->setAttribute($attribute, $value);
        }

        return (bool) $out;
    }

    /**
     * Set a specific attribute.
     */
    public function setAttribute($attribute, $value)
    {
        return $this->getConnection()->setAttribute($attribute, $value);
    }

    /**
     * Retrieve all database connection attributes.
     */
    public function getAttributes(): array
    {
        $out        = [];
        $attributes = [
             0 => 'AUTOCOMMIT',
             1 => 'CASE',
             2 => 'CLIENT_VERSION',
             3 => 'CONNECTION_STATUS',
             4 => 'DRIVER_NAME',
             5 => 'ERRMODE',
             6 => 'ORACLE_NULLS',
             7 => 'PERSISTENT',
             8 => 'PREFETCH',
             9 => 'SERVER_INFO',
            10 => 'SERVER_VERSION',
            11 => 'TIMEOUT'
        ];

        foreach ($attributes as $attribute) {
            $out[$attribute] = $this->getAttribute($attribute);
        }

        return $out;
    }

    /**
     * Retrieve a database connection attribute.
     */
    public function getAttribute(string $attribute)
    {
        return
            $this
                ->getConnection()
                ->getAttribute(constant('\PDO::ATTR_' . $attribute));
    }
}

/**
 * Flex entity.
 */
Consistency::flexEntity(Pdo::class);
