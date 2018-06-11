<?php
/**
 * Pinoco: makes existing static web site dynamic transparently.
 * Copyright 2010-2012, Hisateru Tanaka <tanakahisateru@gmail.com>
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP Version 5
 *
 * @author     Hisateru Tanaka <tanakahisateru@gmail.com>
 * @copyright  Copyright 2010-2012, Hisateru Tanaka <tanakahisateru@gmail.com>
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @package    Pinoco
 */

/**
 * @package Pinoco
 * @property-read string $value Validated value.
 * @property-read string $test Reason of failed by test name.
 * @property-read boolean $valid Totally valid.
 * @property-read boolean $invalid Totally invalid.
 * @property-read string $message Error message when invalid.
 */
class Pinoco_ValidatorContext extends Pinoco_DynamicVars
{
    private $_validator;
    private $_name;
    private $_label;

    private $_filtered;
    private $_filteredValue;

    private $_valid;
    private $_test;
    private $_message;

    /**
     * Constructor
     *
     * @param Pinoco_Validator $validator
     * @param string $name
     * @param string|bool $label
     */
    public function __construct($validator, $name, $label = false)
    {
        parent::__construct();
        $this->_validator = $validator;
        $this->_name = $name;
        $this->_label = $label ? $label : $name;

        $this->_filtered = false;
        $this->_filteredValue = null;

        $this->_valid = true;
        $this->_test = null;
        $this->_message = null;
    }

    /**
     * Retrieves target value.
     *
     * @return mixed
     */
    public function get_value()
    {
        if ($this->_filtered) {
            return $this->_filteredValue;
        } else {
            if (($r = $this->_validator->fetchExistenceAndValue($this->_name)) === null) {
                return null;
            }
            list($exists, $value) = $r;
            return $exists ? $value : null;
        }
    }

    /**
     * Failed test.
     *
     * @return string
     */
    public function get_test()
    {
        return $this->_test;
    }

    /**
     * Valid or not.
     *
     * @return boolean
     */
    public function get_valid()
    {
        return $this->_valid;
    }

    /**
     * Inverse of valid.
     *
     * @return boolean
     */
    public function get_invalid()
    {
        return !$this->_valid;
    }

    /**
     * Error message for the first failed check.
     *
     * @return string
     */
    public function get_message()
    {
        return $this->_message;
    }

    private function buildMessage($template, $param, $value, $label)
    {
        if (is_callable($template)) {
            return call_user_func($template, $param, $value, $label);
        } else {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            return str_replace(
                array('{param}', '{value}', '{label}'),
                array(strval($param), strval($value), $label),
                $template
            );
        }
    }

    private function parseExpression($expression)
    {
        if (is_string($expression) && !empty($expression)) {
            $param = explode(' ', trim($expression));
            $name = array_shift($param);
            $param = count($param) == 0 ? null : implode(' ', $param);
            return array($name, $param);
        } else {
            // $expression is expected to be callable object
            return array($expression, "");
        }
    }

    /**
     * Check the field by specified test.
     *
     * @param string $test
     * @param string|bool $message
     * @return Pinoco_ValidatorContext
     */
    public function is($test, $message = false)
    {
        if (!$this->_valid) {
            return $this;
        }
        list($testName, $param) = $this->parseExpression($test);
        list($result, $value) = $this->_validator->execValidityTest(
            $this->_name,
            $this->_filtered,
            $this->_filteredValue,
            $testName,
            $param
        );
        if (!$result) {
            $this->_test = $test;
            $this->_valid = false;
            $template = $message ? $message : $this->_validator->getMessageFor($testName);
            $this->_message = $this->buildMessage($template, $param, $value, $this->_label);
        }
        return $this;
    }

    /**
     * Check if all elements pass specified test.
     *
     * @param string|callable $test
     * @param string|bool $message
     * @return Pinoco_ValidatorContext
     */
    public function all($test, $message = false)
    {
        if (!$this->_valid) {
            return $this;
        }
        list($testName, $param) = $this->parseExpression($test);
        list($result, $value) = $this->_validator->execValidityTestAll(
            $this->_name,
            $this->_filtered,
            $this->_filteredValue,
            $testName,
            $param
        );
        if (!$result) {
            $this->_test = $test;
            $this->_valid = false;
            $template = $message ? $message : $this->_validator->getMessageFor($testName);
            $this->_message = $this->buildMessage($template, $param, $value, $this->_label);
        }
        return $this;
    }

    /**
     * Check if any element(s) pass(es) specified test.
     *
     * @param string|callable $test
     * @param string|bool $message
     * @return Pinoco_ValidatorContext
     */
    public function any($test, $message = false)
    {
        if (!$this->_valid) {
            return $this;
        }
        list($testName, $param) = $this->parseExpression($test);
        list($result, $value) = $this->_validator->execValidityTestAny(
            $this->_name,
            $this->_filtered,
            $this->_filteredValue,
            $testName,
            $param
        );
        if (!$result) {
            $this->_test = $test;
            $this->_valid = false;
            $template = $message ? $message : $this->_validator->getMessageFor($testName);
            $this->_message = $this->buildMessage($template, $param, $value, $this->_label);
        }
        return $this;
    }

    /**
     * Converts value format for trailing statements.
     *
     * @param mixed $filter
     * @return Pinoco_ValidatorContext
     */
    public function filter($filter)
    {
        if (!$this->_valid) {
            return $this;
        }
        list($filterName, $param) = $this->parseExpression($filter);
        list($filtered, $value) = $this->_validator->execFilter(
            $this->_name,
            $this->_filtered,
            $this->_filteredValue,
            $filterName,
            $param
        );
        if ($filtered) {
            $this->_filtered = $this->_filtered || true;
            $this->_filteredValue = $value;
        }
        return $this;
    }

    /**
     * Converts value formats in the element for trailing statements.
     *
     * @param mixed $filter
     * @return Pinoco_ValidatorContext
     */
    public function map($filter)
    {
        if (!$this->_valid) {
            return $this;
        }
        list($filterName, $param) = $this->parseExpression($filter);
        list($filtered, $value) = $this->_validator->execFilterMap(
            $this->_name,
            $this->_filtered,
            $this->_filteredValue,
            $filterName,
            $param
        );
        if ($filtered) {
            $this->_filtered = $this->_filtered || true;
            $this->_filteredValue = $value;
        }
        return $this;
    }
}
