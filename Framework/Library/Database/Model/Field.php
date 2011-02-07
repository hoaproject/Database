<?php

/**
 * Hoa Framework
 *
 *
 * @license
 *
 * GNU General Public License
 *
 * This file is part of Hoa Open Accessibility.
 * Copyright (c) 2007, 2010 Ivan ENDERLIN. All rights reserved.
 *
 * HOA Open Accessibility is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * HOA Open Accessibility is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with HOA Open Accessibility; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 *
 * @category    Framework
 * @package     Hoa_Database
 * @subpackage  Hoa_Database_Model_Field
 *
 */

/**
 * Hoa_Database
 */
import('Database.~');

/**
 * Hoa_Database_Model_Exception
 */
import('Database.Model.Exception');

/**
 * Hoa_Database_Model_Join
 */
import('Database.Model.Join');

/**
 * Hoa_Database_Constraint_Field
 */
import('Database.Constraint.Field');

/**
 * Hoa_Database_Criterion_Predicate
 */
import('Database.Criterion.Predicate');

/**
 * Hoa_Database_QueryBuilder_Field
 */
import('Database.QueryBuilder.Field');

/**
 * Hoa_Database_Constraint_User_Exception
 */
import('Database.Constraint.User.Exception');

/**
 * Class Hoa_Database_Model_Field.
 *
 * Class that represents a field.
 *
 * @author      Ivan ENDERLIN <ivan.enderlin@hoa-project.net>
 * @copyright   Copyright (c) 2007, 2010 Ivan ENDERLIN.
 * @license     http://gnu.org/licenses/gpl.txt GNU GPL
 * @since       PHP 5
 * @version     0.1
 * @package     Hoa_Database
 * @subpackage  Hoa_Database_Model_Field
 */

class Hoa_Database_Model_Field extends Hoa_Database_QueryBuilder_Field {

    /**
     * The field name.
     *
     * @var Hoa_Database_Model_Field string
     */
    protected $_name       = null;

    /**
     * The field value (after submiting the SQL query).
     *
     * @var Hoa_Database_Model_Field string
     */
    protected $_value      = null;

    /**
     * The table that contains this field.
     *
     * @var Hoa_Database_Model_Table object
     */
    protected $_table      = null;

    /**
     * The constraint instance.
     *
     * @var Hoa_Database_Constraint_Field object
     */
    protected $_constraint = null;

    /**
     * The criterion instance.
     *
     * @var Hoa_Database_Criterion_Predicate object
     */
    protected $_criterion  = null;



    /**
     * Set the table instance that contains this field, name, and constraints.
     * If name does not exists, an exception will be thrown.
     *
     * @access  public
     * @param   array                     $constraints    Must be an array that
     *                                                    describes constraints.
     * @param   Hoa_Database_Model_Table  $table          The table instance, that
     *                                                    contains this field.
     * @return  array
     * @throw   Hoa_Database_Model_Exception
     */
    public function __construct ( Array                    $constraints,
                                  Hoa_Database_Model_Table $table ) {

        $this->setTable($table);

        if(!isset($constraints['name']))
            throw new Hoa_Database_Model_Exception(
                'A field name must be specified.', 0);

        $this->setName($constraints['name']);
        unset($constraints['name']);
        $this->setConstraints($constraints);

        $this->setCriterion();

        return;
    }

    /**
     * Set the field name.
     *
     * @access  protected
     * @param   string     $name    The field name.
     * @return  string
     */
    protected function setName ( $name ) {

        $old         = $this->_name;
        $this->_name = $name;

        return $old;
    }

    /**
     * Set the field value.
     * If the user check constraints method exists, it will be called before
     * setting the value.
     * The user check constraints method matches the following pattern :
     *     user<FieldName>Constraint
     * where <FieldName> is the field name (please, see the
     * self::getName() method), with the first char in uppercase.
     * This method must be declared in a public context.
     * If this method throws an exception, it must be a
     * Hoa_Database_Constraint_User_Exception instance.
     * Finally, if the method returns a value, it will replace the current
     * value.
     *
     * @access  public
     * @param   string  $value    The field value.
     * @return  mixed
     * @throw   Hoa_Database_Model_Exception
     */
    public function setValue ( $value ) {

        $userConstraintName = Hoa_Database::getInstance()
                                  ->getParameter(
                                        'constraint.methodname',
                                        $this->getName()
                                    );

        if(method_exists($this->getTable(), $userConstraintName)) {

            try {

                $value = $this->getTable()->$userConstraintName($value);
            }
            catch ( Hoa_Database_Constraint_User_Exception $e ) {

                throw new Hoa_Database_Model_Exception(
                    'Try to set the %s field value. Constraints are not respected. ' .
                    'The exception message is : %s',
                    $e->getCode(),
                    array($this->getIdentifier(), $e->getFormattedMessage()));
            }
        }

        $old          = $this->_value;
        $this->_value = $value;

        return $old;
    }

    /**
     * Set the table instance that contains this field.
     * It should not be in a public context but it is hard to make it on an
     * other way …
     *
     * @access  public
     * @param   Hoa_Database_Model_Table  $table    The table instance.
     * @return  Hoa_Database_Model_Table
     */
    public function setTable ( Hoa_Database_Model_Table $table ) {

        $old          = $this->_table;
        $this->_table = $table;

        return $old;
    }

    /**
     * Set the field constraints.
     *
     * @access  protected
     * @param   array      $constraints    An array that describes the field
     *                                     constraints.
     * @return  Hoa_Database_Constraint_Field
     */
    protected function setConstraints ( Array $constraints ) {

        $old               = $this->_constraint;
        $this->_constraint = new Hoa_Database_Constraint_Field($this, $constraints);

        return $old;
    }

    /**
     * Set the criterion.
     *
     * @access  protected
     * @return  Hoa_Database_Criterion_Predicate
     */
    protected function setCriterion ( ) {

        $old              = $this->_criterion;
        $this->_criterion = new Hoa_Database_Criterion_Predicate($this);

        return $old;
    }

    /**
     * Get the field name.
     *
     * @access  public
     * @return  string
     */
    public function getName ( ) {

        return $this->_name;
    }

    /**
     * Get identifier, i.e. “table.attribut” name.
     *
     * @access  public
     * @return  string
     */
    public function getIdentifier ( ) {

        return $this->getTable()->getName() . '.' . $this->getName();
    }

    /**
     * Get the field value.
     *
     * @access  public
     * @return  mixed
     */
    public function getValue ( ) {

        return $this->_value;
    }

    /**
     * Get the table instance that contains this field.
     *
     * @access  public
     * @return  Hoa_Database_Model_Table
     */
    public function getTable ( ) {

        return $this->_table;
    }

    /**
     * Get the table name that contains this field.
     *
     * @access  public
     * @return  string
     */
    public function getTableName ( ) {

        return $this->getTable()->getName();
    }

    /**
     * Get the table name that contains this field, with the AS clause if the
     * table was renamed.
     *
     * @access  public
     * @return  string
     */
    public function getTableNameWithAs ( ) {

        return $this->getTable()->getNameWithAs();
    }

    /**
     * Get the field constraint instance.
     *
     * @access  protected
     * @return  Hoa_Database_Constraint_Field
     */
    public function getConstraint ( ) {

        return $this->_constraint;
    }

    /**
     * Get the criterion instance.
     *
     * @access  public
     * @return  Hoa_Database_Criterion_Predicate
     */
    public function getCriterion ( ) {

        return $this->_criterion;
    }

    /**
     * All undeclared methods might be a criterion method.
     *
     * @access  public
     * @param   string  $name     The method name.
     * @param   array   $value    The method arguments.
     * @return  Hoa_Database_Model_Table
     * @throw   Hoa_Database_Model_Exception
     */
    public function __call ( $name, $value ) {

        if(!method_exists($this->getCriterion(), $name))
            throw new Hoa_Database_Model_Exception(
                'Cannot call the %s criterion on fields.', 1, $name);

        call_user_func_array(
            array(
                $this->getCriterion(),
                $name
            ),
            $value
        );

        return $this->getTable();
    }

    /**
     * Rename a field (apply the AS clause).
     * It will create and return a new field (and not destroy this field).
     *
     * @access  public
     * @param   string  $name    The new field name.
     * @return  Hoa_Database_Model_Field
     * @throw   Hoa_Database_Model_Exception
     */
    public function rename ( $name ) {

        return $this->getTable()->getField(
            $this->getTable()->addField(
                $name,
                $this->getConstraint()->getConstraints()
            )
        );
    }

    /**
     *
     * Not for now … sorry.
     * It is too complex to be well-made for this time.
     *
     *
     *  public function innerJoin ( ) {
     *
     *      if(false === $this->getConstraint()->isForeign())
     *          throw new Hoa_Database_Model_Exception(
     *              'Cannot make a join on a no-foreign key.', 2);
     *
     *
     *      list($table, $field) = explode('.', $this->getConstraint()->getForeign());
     *
     *      $join  = new Hoa_Database_Model_Join(
     *                   $this,
     *                   Hoa_Database::getInstance()->getTable(
     *                       $this->getTable()->getBaseName() . '.' . $table
     *                   )->$field,
     *                   Hoa_Database_Model_Join::INNER
     *               );
     *
     *      $this->getTable()->addLinkedTable($join);
     *
     *      return $join;
     *  }
     *
     */
}