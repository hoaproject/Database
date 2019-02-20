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

namespace Hoa\Database;

use Hoa\Consistency;
use Hoa\Event;
use Hoa\Zformat;

/**
 * Class \Hoa\Database\Dal.
 *
 * The higher class of the Database Abstract Layer. It wrappes all DAL.
 */
class Dal implements Zformat\Parameterizable, Event\Source
{
    /**
     * Abstract layer: DBA.
     *
     * @const string
     */
    const DBA  = 'Dba';

    /**
     * Abstract layer: DBX.
     *
     * @const string
     */
    const DBX  = 'Dbx';

    /**
     * Abstract layer: Odbc.
     *
     * @const string
     */
    const ODBC = 'Odbc';

    /**
     * Abstract layer: PDO.
     *
     * @const string
     */
    const PDO  = 'Pdo';

    /**
     * Multiton.
     *
     * @var array
     */
    private static $_instance        = [];

    /**
     * Current singleton ID.
     *
     * @var string
     */
    private static $_id              = null;

    /**
     * Current ID.
     *
     * @var string
     */
    protected $__id                  = null;

    /**
     * The layer instance.
     *
     * @var ?IDal\Wrapper
     */
    protected $_layer                = null;

    /**
     * Parameter of \Hoa\Database\Dal.
     *
     * @var ?Zformat\Parameter
     */
    protected static $_parameters    = null;

    /**
     * The layer connection parameter.
     *
     * @var array
     */
    protected $_connectionParameters = [];



    /**
     * Create a DAL instance, representing a connection to a database.
     * The constructor is private to make a multiton.
     */
    private function __construct(array $connectionParameters)
    {
        $this->_connectionParameters = $connectionParameters;

        $id    = $this->__id = self::$_id;
        $event = 'hoa://Event/Database/' . $id;

        Event::register($event . ':opened', $this);
        Event::register($event . ':closed', $this);

        return;
    }

    /**
     * Initialize parameters.
     */
    public static function initializeParameters(array $parameters = [])
    {
        self::$_parameters = new Zformat\Parameter(
            __CLASS__,
            [],
            [
                /**
                 * Example:
                 *   'connection.list.default.dal'      => Dal::PDO,
                 *   'connection.list.default.dsn'      => 'sqlite:hoa://Data/Variable/Database/Foo.sqlite',
                 *   'connection.list.default.username' => '',
                 *   'connection.list.default.password' => '',
                 *   'connection.list.default.options'  => null,
                 */

                'connection.autoload' => null // or connection ID, e.g. 'default'.
            ]
        );
        self::$_parameters->setParameters($parameters);

        return;
    }

    /**
     * Make a multiton on the $id.
     */
    public static function getInstance(
        string $id,
        string $dalName             = null,
        string $dsn                 = null,
        string $username            = null,
        string $password            = null,
        array $driverOptions = []
    ): self {
        if (null === self::$_parameters) {
            self::initializeParameters();
        }

        self::$_id = $id;

        if (isset(self::$_instance[$id])) {
            return self::$_instance[$id];
        }

        if (null === $dalName  &&
            null === $dsn      &&
            null === $username &&
            null === $password &&
            empty($driverOptions)) {
            $list = self::$_parameters->unlinearizeBranch('connection.list');

            if (!isset($list[$id])) {
                throw new Exception(
                    'Connection ID %s does not exist in the connection list.',
                    0,
                    $id
                );
            }

            $handle        = $list[$id];
            $dalName       = @$handle['dal']      ?: 'Undefined';
            $dsn           = @$handle['dsn']      ?: '';
            $username      = @$handle['username'] ?: '';
            $password      = @$handle['password'] ?: '';
            $driverOptions = @$handle['options']  ?: [];
        }

        return self::$_instance[$id] = new self([
            $dalName,
            $dsn,
            $username,
            $password,
            $driverOptions
        ]);
    }

    /**
     * Get the last instance of a DAL, i.e. the last used singleton.
     * If no instance was set but if the connection.autoload parameter is set,
     * then we auto-connect (autoload) a connection.
     */
    public static function getLastInstance(): IDal\Wrapper
    {
        if (null === self::$_parameters) {
            self::initializeParameters();
        }

        if (null === self::$_id) {
            $autoload = self::$_parameters->getFormattedParameter(
                'connection.autoload'
            );

            if (null !== $autoload) {
                self::getInstance($autoload);
            }
        }

        if (null === self::$_id) {
            throw new Exception(
                'No instance was set, cannot return the last instance.',
                1
            );
        }

        return self::$_instance[self::$_id];
    }

    /**
     * Get parameters.
     */
    public function getParameters(): ?Zformat\Parameter
    {
        return self::$_parameters;
    }

    /**
     * Open a connection to the database.
     */
    private function open()
    {
        list(
            $dalName,
            $dsn,
            $username,
            $password,
            $driverOptions
        ) = $this->_connectionParameters;

        // Please see https://bugs.php.net/55154.
        if (0 !== preg_match('#^sqlite:(.+)$#i', $dsn, $matches)) {
            $dsn = 'sqlite:' . resolve($matches[1]);
        }

        $this->setDal(
            Consistency\Autoloader::dnew(
                'Hoa\Database\Layer\\' . $dalName,
                [$dsn, $username, $password, $driverOptions]
            )
        );

        $id = $this->getId();
        Event::notify(
            'hoa://Event/Database/' . $id . ':opened',
            $this,
            new Event\Bucket([
                'id'            => $id,
                'dsn'           => $dsn,
                'username'      => $username,
                'driverOptions' => $driverOptions
            ])
        );

        return;
    }

    /**
     * Close connection to the database.
     */
    public function close(): bool
    {
        $id    = $this->getId();
        $event = 'hoa://Event/Database/' . $id;

        $this->_layer = null;
        self::$_id    = null;
        unset(self::$_instance[$id]);

        Event::notify(
            $event . ':closed',
            $this,
            new Event\Bucket(['id' => $id])
        );

        Event::unregister($event . ':opened');
        Event::unregister($event . ':closed');

        return true;
    }

    /**
     * Set database abstract layer instance.
     */
    protected function setDal(IDal\Wrapper $dal): ?IDal\Wrapper
    {
        $old          = $this->_layer;
        $this->_layer = $dal;

        return $old;
    }

    /**
     * Get the database abstract layer instance.
     */
    protected function getDal(): ?IDal\Wrapper
    {
        if (null === $this->_layer) {
            $this->open();
        }

        return $this->_layer;
    }

    /**
     * Initiate a transaction.
     */
    public function beginTransaction(): bool
    {
        return $this->getDal()->beginTransaction();
    }

    /**
     * Commit a transaction.
     */
    public function commit(): bool
    {
        return $this->getDal()->commit();
    }

    /**
     * Roll back a transaction.
     */
    public function rollBack(): bool
    {
        return $this->getDal()->rollBack();
    }

    /**
     * Return the ID of the last inserted row or sequence value.
     */
    public function lastInsertId(string $name = null): string
    {
        if (null === $name) {
            return $this->getDal()->lastInsertId();
        }

        return $this->getDal()->lastInsertId($name);
    }

    /**
     * Prepare a statement for execution and returns a statement object.
     */
    public function prepare(string $statement, array $options = []): DalStatement
    {
        return new DalStatement(
            $this->getDal()->prepare(
                $statement, $options
            )
        );
    }

    /**
     * Quote a string for use in a query.
     */
    public function quote(?string $string, int $type = -1): string
    {
        if ($type < 0) {
            return $this->getDal()->quote($string);
        }

        return $this->getDal()->quote($string, $type);
    }

    /**
     * Execute an SQL statement, returning a result set as a
     * \Hoa\Database\DalStatement object.
     */
    public function query(string $statement): DalStatement
    {
        return new DalStatement(
            $this->getDal()->query($statement)
        );
    }

    /**
     * Fetch the SQLSTATE associated with the last operation on the database
     * handle.
     */
    public function errorCode(): string
    {
        return $this->getDal()->errorCode();
    }

    /**
     * Fetch extends error information associated with the last operation on the
     * database handle.
     */
    public function errorInfo(): array
    {
        return $this->getDal()->errorInfo();
    }

    /**
     * Return an array of available drivers.
     */
    public function getAvailableDrivers(): array
    {
        return $this->getDal()->getAvailableDrivers();
    }

    /**
     * Set attributes.
     */
    public function setAttributes(array $attributes): array
    {
        return $this->getDal()->setAttributes($attributes);
    }

    /**
     * Set a specific attribute.
     */
    public function setAttribute($attribute, $value)
    {
        return $this->getDal()->setAttribute($attribute, $value);
    }

    /**
     * Retrieve all database connection attributes.
     */
    public function getAttributes(): array
    {
        return $this->getDal()->getAttributes();
    }

    /**
     * Retrieve a database connection attribute.
     */
    public function getAttribute(string $attribute)
    {
        return $this->getDal()->getAttribute($attribute);
    }

    /**
     * Get current ID.
     */
    public function getId(): string
    {
        return $this->__id;
    }
}
