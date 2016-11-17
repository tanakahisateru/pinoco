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
 * Procedural validation utility.
 *
 * <code>
 * $validator = new Pinoco_Validator($data);
 * $validator->check('name')->is('not-empty')->is('max-length 255');
 * $validator->check('age')->is('not-empty')->is('integer')
 *                         ->is('>= 21', 'Adult only.');
 * if ($validator->valid) {
 *     echo "OK";
 * }
 * else {
 *     foreach ($validator->errors as $field=>$context) {
 *         echo $field . ":" . $context->message . "\n";
 *     }
 * }
 * </code>
 *
 * Builtin tests:
 *   pass, fail, empty, not-empty, max-length, min-length, in a,b,c, not-in a,b,c,
 *   numeric, integer, alpha, alpha-numeric, == n, != n, > n, >= n, < n,  <= n,
 *   match /regexp/, not-match /regexp/, email, url
 *
 * @package Pinoco
 * @property-read Pinoco_Vars $result All context objects.
 * @property-read Pinoco_Vars $errors Invalid context objects only.
 * @property-read Pinoco_Vars $values Validated values unwrapped.
 * @property-read boolean $valid   Totally valid or not.
 * @property-read boolean $invalid Totally invalid or not.
 */
class Pinoco_Validator extends Pinoco_DynamicVars
{
    protected $_tests;
    protected $_filters;
    protected $_messages;

    private $_target;
    private $_result;
    private $_errors;
    private $_values;

    /**
     * Constructor
     *
     * @param string $target
     * @param array $messages
     */
    public function __construct($target, $messages=array())
    {
        parent::__construct();

        $this->_tests = array();
        $this->_setupBuiltinTests();

        $this->_filters = array();
        $this->_setupBuiltinFilters();

        $this->_messages = array();
        $this->overrideErrorMessages($messages);

        $this->_target = $target;
        $this->_result = new Pinoco_Vars();
        $this->_errors = null;
        $this->_values = null;
    }

    private function _setupBuiltinTests()
    {
        // builtin testers
        $this->defineValidityTest('pass', array($this, '_testPassComplex'),
            "Valid.", true);
        $this->defineValidityTest('fail', array($this, '_testFailComplex'),
            "Invalid.", true);
        $this->defineValidityTest('empty', array($this, '_testEmptyComplex'),
            "Leave as empty.", true);
        $this->defineValidityTest('not-empty', array($this, '_testNotEmptyComplex'),
            "Required.", true);
        $this->defineValidityTest('max-length', array($this, '_testMaxLength'),
            "In {param} letters.");
        $this->defineValidityTest('min-length', array($this, '_testMinLength'),
            "At least {param} letters.");
        $this->defineValidityTest('in', array($this, '_testIn'),
            "Choose in {param}.");
        $this->defineValidityTest('not-in', array($this, '_testNotIn'),
            "Choose else of {param}.");
        $this->defineValidityTest('numeric', array($this, '_testNumeric'),
            "By number.");
        $this->defineValidityTest('integer', array($this, '_testInteger'),
            "By integer number.");
        $this->defineValidityTest('alpha', array($this, '_testAlpha'),
            "Alphabet only.");
        $this->defineValidityTest('alpha-numeric', array($this, '_testAlphaNumeric'),
            "Alphabet or number.");
        $this->defineValidityTest('array', array($this, '_testArray'),
            "By Array.");
        $this->defineValidityTest('==', array($this, '_testEqual'),
            "Shuld equal to {param}.");
        $this->defineValidityTest('!=', array($this, '_testNotEqual'),
            "Should not equal to {param}.");
        $this->defineValidityTest('>', array($this, '_testGreaterThan'),
            "Greater than {param}.");
        $this->defineValidityTest('>=', array($this, '_testGreaterThanOrEqual'),
            "Greater than or equals to {param}.");
        $this->defineValidityTest('<', array($this, '_testLessorThan'),
            "Lessor than {param}.");
        $this->defineValidityTest('<=', array($this, '_testLessorThanOrEqual'),
            "Lessor than or equals to {param}.");
        $this->defineValidityTest('match', array($this, '_testMatch'),
            "Invalid pattern.");
        $this->defineValidityTest('not-match', array($this, '_testNotMatch'),
            "Not allowed pattern.");
        $this->defineValidityTest('email', array($this, '_testEmail'),
            "Email only.");
        $this->defineValidityTest('url', array($this, '_testUrl'),
            "URL only.");
    }

    private function _setupBuiltinFilters()
    {
        // builtin filters
        $this->defineFilter('trim', array($this, '_filterTrim'));
        $this->defineFilter('ltrim', array($this, '_filterLtrim'));
        $this->defineFilter('rtrim', array($this, '_filterRtrim'));
    }

    /**
     * Defines custom test.
     *
     * @param string $testName
     * @param callback $callable
     * @param string $message
     * @param boolean $complex
     * @return void
     */
    public function defineValidityTest($testName, $callable, $message, $complex=false)
    {
        $this->_tests[$testName] = array(
            'callback' => $callable,
            'message' => $message,
            'complex' => $complex,
        );
    }

    /**
     * Defines custom filter.
     *
     * @param string $filterName
     * @param callback $callable
     * @param boolean $complex
     * @return void
     */
    public function defineFilter($filterName, $callable, $complex=false)
    {
        $this->_filters[$filterName] = array(
            'callback' => $callable,
            'complex' => $complex,
        );
    }

    /**
     * Overrides error messages.
     *
     * @param array $messages
     * @return void
     */
    public function overrideErrorMessages($messages)
    {
        foreach ($messages as $test=>$msg) {
            $this->_messages[$test] = $msg;
        }
    }

    /**
     * Resolve error message by test name.
     *
     * @param string $testName
     * @return string
     */
    public function getMessageFor($testName)
    {
        if (is_scalar($testName)) {
            if (isset($this->_messages[$testName])) {
                return $this->_messages[$testName];
            }
            elseif (isset($this->_tests[$testName])) {
                return $this->_tests[$testName]['message'];
            }
            else {
                return 'not registered';
            }
        }
        else {
            return 'invalid';
        }
    }

    /**
     * Check existence and fetch value at the same time.
     * (called by self and validation context)
     *
     * @param string $name
     * @return array
     */
    public function fetchExistenceAndValue($name)
    {
        //type check
        if ($this->_target instanceof Pinoco_Vars) {
            $exists = $this->_target->has($name);
            $value = $this->_target->get($name);
        }
        elseif ($this->_target instanceof Pinoco_List) {
            $exists = intval($name) < $this->_target->count();
            $value = $exists ? $this->_target[$name] : null;
        }
        elseif (is_array($this->_target)) {
            $exists = isset($this->_target[$name]);
            $value = $exists ? $this->_target[$name] : null;
        }
        elseif (is_object($this->_target)) {
            $exists = isset($this->_target->$name);
            $value = $exists ? $this->_target->$name : null;
        }
        else {
            $exists = false;
            $value = null;
        }
        return array($exists, $value);
    }

    private function prepareCallback($methods, $methodName, $param)
    {
        $this->_errors = null;
        $this->_values = null;
        if (is_scalar($methodName) && isset($methods[$methodName])) {
            return array(
                $methods[$methodName]['callback'],
                $methods[$methodName]['complex'],
                array($param)
            );
        }
        elseif (is_callable($methodName)) {
            return array(
                $methodName,
                false,
                $param ? explode(' ', $param) : array()
            );
        }
        else {
            // test method not registered
            return array(
                null,
                false,
                array()
            );
        }
    }

    private function prepareValue($field, $filtered, $filteredValue)
    {
        // fetch
        if ($filtered) {
            return array(
                true,
                $filteredValue
            );
        }
        else {
            return $this->fetchExistenceAndValue($field);
        }
    }

    private function callMethod($callable, $complex, $params, $exists, $value, $forFilter=false)
    {
        if ($complex) {
            // complex test: full information presented
            //               and should be checked if empty or not
            $args = array($exists, $value);
        }
        else {
            if (!$forFilter) {
                // simple test: empty always success
                if (!$exists || empty($value) && !($value === "0" || $value === 0 || $value === false || $value === array())) {
                    // validation must be passed and value is as is.
                    return array(true, $value);
                }
            }
            $args = array($value);
        }
        foreach ($params as $p) {
            $args[] = $p;
        }
        return call_user_func_array($callable, $args);
    }

    /**
     * Executes validation test.
     * (called by validation context)
     *
     * @param string $field
     * @param boolean $filtered
     * @param mixed $filteredValue
     * @param string $testName
     * @param string $param
     * @return array
     */
    public function execValidityTest($field, $filtered, $filteredValue, $testName, $param)
    {
        list($callable, $complex, $params) = $this->prepareCallback($this->_tests, $testName, $param);
        list($exists, $value) = $this->prepareValue($field, $filtered, $filteredValue);
        if ($callable == null) {
            return array(false, $value);
        }
        else {
            return array(
                $this->callMethod($callable, $complex, $params, $exists, $value),
                $value
            );
        }
    }

    /**
     * Executes validation test to array with logical AND.
     * (called by validation context)
     *
     * @param string $field
     * @param boolean $filtered
     * @param mixed $filteredValue
     * @param string $testName
     * @param string $param
     * @return array
     */
    public function execValidityTestAll($field, $filtered, $filteredValue, $testName, $param)
    {
        list($callable, $complex, $params) = $this->prepareCallback($this->_tests, $testName, $param);
        list($exists, $value) = $this->prepareValue($field, $filtered, $filteredValue);
        if ($callable == null || !(is_array($value) || $value instanceof Traversable)) {
            return array(false, $value);
        }
        else {
            foreach ($value as $v) {
                $result = $this->callMethod($callable, $complex, $params, $exists, $v);
                if (!$result) {
                    return array(false, $value);
                }
            }
            return array(true, $value);
        }
    }

    /**
     * Executes validation test to array with logical OR.
     * (called by validation context)
     *
     * @param string $field
     * @param boolean $filtered
     * @param mixed $filteredValue
     * @param string $testName
     * @param string $param
     * @return array
     */
    public function execValidityTestAny($field, $filtered, $filteredValue, $testName, $param)
    {
        list($callable, $complex, $params) = $this->prepareCallback($this->_tests, $testName, $param);
        list($exists, $value) = $this->prepareValue($field, $filtered, $filteredValue);
        if ($callable == null || !(is_array($value) || $value instanceof Traversable)) {
            return array(false, $value);
        }
        else {
            foreach ($value as $v) {
                $result = $this->callMethod($callable, $complex, $params, $exists, $v);
                if ($result) {
                    return array(true, $value);
                }
            }
            return array(false, $value);
        }
    }

    /**
     * Executes filter.
     * (called by validation context)
     *
     * @param string $field
     * @param boolean $filtered
     * @param mixed $filteredValue
     * @param mixed $filterName
     * @param string $param
     * @return array
     */
    public function execFilter($field, $filtered, $filteredValue, $filterName, $param)
    {
        list($callable, $complex, $params) = $this->prepareCallback($this->_filters, $filterName, $param);
        list($exists, $value) = $this->prepareValue($field, $filtered, $filteredValue);
        if ($callable == null) {
            return array(true, null);
        }
        else {
            return array(
                true,
                $this->callMethod($callable, $complex, $params, $exists, $value, true)
            );
        }
    }

    /**
     * Executes filter for each elements.
     * (called by validation context)
     *
     * @param string $field
     * @param boolean $filtered
     * @param mixed $filteredValue
     * @param mixed $filterName
     * @param string $param
     * @return array
     */
    public function execFilterMap($field, $filtered, $filteredValue, $filterName, $param)
    {
        list($callable, $complex, $params) = $this->prepareCallback($this->_filters, $filterName, $param);
        list($exists, $value) = $this->prepareValue($field, $filtered, $filteredValue);
        if ($callable == null || !(is_array($value) || $value instanceof Traversable)) {
            return array(true, null);
        }
        else {
            if ($value instanceof Pinoco_List) {
                $result = new Pinoco_List();
                foreach ($value as $v) {
                    $result->push($this->callMethod($callable, $complex, $params, $exists, $v, true));
                }
            }
            else {
                $result = array();
                foreach ($value as $v) {
                    $result[] = $this->callMethod($callable, $complex, $params, $exists, $v, true);
                }
            }
            return array(true, $result);
        }
    }

    /**
     * Returns independent validation context.
     *
     * @param string $name
     * @param string|bool $label
     * @return Pinoco_ValidatorContext
     */
    public function contextFor($name, $label=false)
    {
        return new Pinoco_ValidatorContext($this, $name, $label);
    }

    /**
     * Starts named property check.
     *
     * @param string $name
     * @param string|bool $label
     * @return Pinoco_ValidatorContext
     */
    public function check($name, $label=false)
    {
        $this->_errors = null;
        $this->_values = null;
        if (!$this->_result->has($name)) {
            $this->_result->set($name, $this->contextFor($name, $label));
        }
        return $this->_result->get($name);
    }

    /**
     * Clears previous result and restarts named property check.
     *
     * @param string $name
     * @param string|bool $label
     * @return Pinoco_ValidatorContext
     */
    public function recheck($name, $label=false)
    {
        $this->_errors = null;
        $this->_values = null;
        $this->_result->set($name, $this->contextFor($name, $label));
        return $this->_result->get($name);
    }

    /**
     * Clears previous result.
     *
     * @param string $name
     * @return void
     */
    public function uncheck($name)
    {
        $this->_errors = null;
        $this->_values = null;
        if ($this->_result->has($name)) {
            $this->_result->remove($name);
        }
    }

    /**
     * Exports test all results.
     *
     * @return Pinoco_Vars
     */
    public function get_result()
    {
        return $this->_result;
    }

    /**
     * Exports test results only failed.
     *
     * @return Pinoco_Vars
     */
    public function get_errors()
    {
        if ($this->_errors === null) {
            $this->_errors = new Pinoco_Vars();
            foreach ($this->_result->keys() as $field) {
                $result = $this->_result->get($field);
                if ($result->invalid) {
                    $this->_errors->set($field, $result);
                }
            }
        }
        return $this->_errors;
    }

    /**
     * Exports test results only failed.
     *
     * @return Pinoco_Vars
     */
    public function get_values()
    {
        if ($this->_values === null) {
            $this->_values = new Pinoco_Vars();
            foreach ($this->_result->keys() as $field) {
                $result = $this->_result->get($field);
                $this->_values->set($field, $result->value);
            }
        }
        return $this->_values;
    }

    /**
     * Returns which all tests succeeded or not.
     *
     * @return boolean
     */
    public function get_valid()
    {
        return ($this->get_errors()->count() == 0);
    }

    /**
     * Returns which validator has one or more failed tests.
     *
     * @return boolean
     */
    public function get_invalid()
    {
        return !$this->get_valid();
    }

    /**
     * Returns all succeeded checking results to be used in form's initial state.
     * If you fetch a field not given by $values, you will get a passed checking
     * context instead.
     *
     * @param array $values
     * @return Pinoco_Vars
     */
    public static function emptyResult($values=array())
    {
        $validator = new self($values);
        foreach ($values as $name=>$value) {
            $validator->check($name)->is('pass');
        }
        $result = $validator->result;
        $result->setDefault($validator->contextFor('any')->is('pass'));
        $result->setLoose(true);
        return $result;
    }

    /////////////////////////////////////////////////////////////////////
    // builtin tests
    private function _testPassComplex($exists, $value)
    {
        return true;
    }
    private function _testFailComplex($exists, $value)
    {
        return false;
    }
    private function _testEmptyComplex($exists, $value)
    {
        if (!$exists || $value === null) { return true; }
        if ($value === "0" || $value === 0 || $value === false || $value === array()) {
            return false;
        }
        $value = trim(mb_convert_kana($value, "s", 'UTF-8'));
        return empty($value);
    }
    private function _testNotEmptyComplex($exists, $value)
    {
        return !$this->_testEmptyComplex($exists, $value);
    }

    private function _testMaxLength($value, $param=0)
    {
        return strlen(strval($value)) <= $param;
    }
    private function _testMinLength($value, $param=0)
    {
        return strlen(strval($value)) >= $param;
    }
    private function _testIn($value, $param='')
    {
        $as = explode(',', $param);
        foreach ($as as $a) {
            if ($value == trim($a)) { return true; }
        }
        return false;
    }
    private function _testNotIn($value, $param='')
    {
        return !$this->_testIn($value, $param);
    }
    private function _testNumeric($value)
    {
        return is_numeric($value);
    }
    private function _testInteger($value)
    {
        return is_integer($value);
    }
    private function _testAlpha($value)
    {
        return ctype_alpha($value);
    }
    private function _testAlphaNumeric($value)
    {
        return ctype_alnum($value);
    }
    private function _testArray($value)
    {
        return is_array($value) || $value instanceof Traversable;
    }
    private function _testEqual($value, $param=null)
    {
        return $value == $param;
    }
    private function _testNotEqual($value, $param=null)
    {
        return !$this->_testEqual($value, $param);
    }
    private function _testGreaterThan($value, $param=0)
    {
        return $value > $param;
    }
    private function _testGreaterThanOrEqual($value, $param=0)
    {
        return $value >= $param;
    }
    private function _testLessorThan($value, $param=0)
    {
        return $value < $param;
    }
    private function _testLessorThanOrEqual($value, $param=0)
    {
        return $value <= $param;
    }
    private function _testMatch($value, $param='/^$/')
    {
        return preg_match($param, $value);
    }
    private function _testNotMatch($value, $param='/^$/')
    {
        return !$this->_testMatch($value, $param);
    }
    private function _testEmail($value)
    {
        return preg_match('/@[A-Z0-9][A-Z0-9_-]*(\.[A-Z0-9][A-Z0-9_-]*)*$/i', $value);
    }
    private function _testUrl($value)
    {
        return preg_match('/^[A-Z]+:\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)*):?(\d+)?\/?/i', $value);
    }

    /////////////////////////////////////////////////////////////////////
    // builtin filters
    private function _filterTrim($value)
    {
        return trim($value);
    }
    private function _filterLtrim($value)
    {
        return ltrim($value);
    }
    private function _filterRtrim($value)
    {
        return rtrim($value);
    }
}

