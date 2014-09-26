<?php

/**
 * Hoa
 *
 *
 * @license
 *
 * New BSD License
 *
 * Copyright © 2007-2014, Ivan Enderlin. All rights reserved.
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

namespace Hoa\Database {

/**
 * Class \Hoa\Database\DalStatement.
 *
 * The heigher class that represents a DAL statement.
 *
 * @author     Ivan Enderlin <ivan.enderlin@hoa-project.net>
 * @author     Raphaël Emourgeon <raphael.emourgeon@hoa-project.net>
 * @copyright  Copyright © 2007-2014 Ivan Enderlin, Raphaël Emourgeon.
 * @license    New BSD License
 */

class DalStatement implements \Hoa\Database\IDal\WrapperStatement {

    /**
     * The statement instance.
     *
     * @var \Hoa\Database\IDal\WrapperStatement object
     */
    protected $statement = null;



    /**
     * Create a statement instance.
     *
     * @access  public
     * @param   \Hoa\Database\IDal\WrapperStatement  $statement    The
     *                                                             statement
     *                                                             instance.
     * @return  void
     */
    public function __construct ( IDal\WrapperStatement $statement ) {

        $this->setStatement($statement);
    }

    /**
     * Set the statement instance.
     *
     * @access  protected
     * @param   \Hoa\Database\IDal\WrapperStatement  $statement    The
     *                                                             statement
     *                                                             instance.
     * @return  \Hoa\Database\IDal\WrapperStatement
     */
    protected function setStatement ( IDal\WrapperStatement $statement ) {

        $old             = $this->statement;
        $this->statement = $statement;

        return $old;
    }

    /**
     * Get the statement instance.
     *
     * @access  protected
     * @return  \Hoa\Database\IDal\WrapperStatement
     */
    protected function getStatement ( ) {

        return $this->statement;
    }

    /**
     * Execute a prepared statement.
     *
     * @access  public
     * @param   array   $bindParameters    Bind parameters values if bindParam is
     *                                     not called.
     * @return  \Hoa\Database\DalStatement
     * @throw   \Hoa\Database\Exception
     */
    public function execute ( Array $bindParameters = array() ) {

        if(empty($bindParameters))
            return $this->getStatement()->execute();

        $this->getStatement()->execute($bindParameters);

        return $this;
    }

    /**
     * Bind a parameter to te specified variable name.
     *
     * @access  public
     * @param   mixed   $parameter    Parameter name.
     * @param   mixed   $value        Parameter value.
     * @param   int     $type         Type of value.
     * @param   int     $length       Length of data type.
     * @return  bool
     * @throw   \Hoa\Database\Exception
     */
    public function bindParameter ( $parameter, &$value, $type = null,
                                    $length = null) {

        if(null === $type)
            return $this->getStatement()->bindParameter($parameter, $value);

        if(null === $length)
            return $this->getStatement()->bindParameter(
                $parameter,
                $value,
                $type
            );

        return $this->getStatement()->bindParameter(
            $parameter,
            $value,
            $type,
            $length
        );
    }

    /**
     * Rewinds iterator cache.
     *
     * @access  public
     * @return  void
     */
    public function rewind ( ) {

        return $this->getStatement()->rewind();
    }

    /**
     * Checks if current row is valid.
     *
     * @access  public
     * @return  bool
     */
    public function valid ( ) {

        return $this->getStatement()->valid();
    }

    /**
     * Return the current row value.
     *
     * @access  public
     * @return  array
     */
    public function current ( ) {

        return $this->getStatement()->current();
    }

    /**
     * Return the current row key.
     *
     * @access  public
     * @return  int
     */
    public function key ( ) {

        return $this->getStatement()->key();
    }

    /**
     * Fetches the next row from a result set.
     *
     * @access  public
     * @return  void
     * @throw   \Hoa\Database\Exception
     */
    public function next ( ) {

        return $this->getStatement()->next();
    }

    /**
     * Return an array containing all of the result set rows.
     *
     * @access  public
     * @return  array[]
     * @throw   \Hoa\Database\Exception
     */
    public function fetchAll ( ) {

        return $this->getStatement()->fetchAll();
    }

    /**
     * Fetch the first row in the result set.
     *
     * @access  public
     * @return  array
     * @throw   \Hoa\Database\Exception
     */
    public function fetchFirst ( ) {

        return $this->getStatement()->fetchFirst();
    }

    /**
     * Fetch the last row in the result set.
     *
     * @access  public
     * @return  array
     * @throw   \Hoa\Database\Exception
     */
    public function fetchLast ( ) {

        return $this->getStatement()->fetchLast();
    }

    /**
     * Fetch the next row in the result set.
     *
     * @access  public
     * @return  array
     * @throw   \Hoa\Database\Exception
     */
    public function fetchNext ( ) {

        return $this->getStatement()->fetchNext();
    }

    /**
     * Fetch the previous row in the result set.
     *
     * @access  public
     * @return  array
     * @throw   \Hoa\Database\Exception
     */
    public function fetchPrior ( ) {

        return $this->getStatement()->fetchPrior();
    }

    /**
     * Return a single column from the next row of the result set or false if
     * there is no more row.
     *
     * @access  public
     * @param   int  $column    Column index.
     * @return  mixed
     * @throw   \Hoa\Database\Exception
     */
    public function fetchColumn ( $column = 0 ) {

        return $this->getStatement()->fetchColumn($column);
    }

    /**
     * Returns the number of rows affected by the last SQL statement.
     *
     * @access  public
     * @return  int
     * @throw   \Hoa\Database\Exception
     */
    public function count ( ) {

        return $this->getStatement()->count();
    }

    /**
     * Close the cursor, enabling the statement to be executed again.
     *
     * @access  public
     * @return  bool
     * @throw   \Hoa\Database\Exception
     */
    public function closeCursor ( ) {

        return $this->getStatement()->closeCursor();
    }

    /**
     * Fetch the SQLSTATE associated with the last operation on the statement
     * handle.
     *
     * @access  public
     * @return  string
     * @throw   \Hoa\Database\Exception
     */
    public function errorCode ( ) {

        return $this->getStatement()->errorCode();
    }

    /**
     * Fetch extends error information associated with the last operation on the
     * statement handle.
     *
     * @access  public
     * @return  array
     * @throw   \Hoa\Database\Exception
     */
    public function errorInfo ( ) {

        return $this->getStatement()->errorInfo();
    }
}

}
