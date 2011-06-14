<?php
/**
 * Pinoco: makes existing static web site dynamic transparently.
 * Copyright 2010-2011, Hisateru Tanaka <tanakahisateru@gmail.com>
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP Version 5
 *
 * @category   Framework
 * @author     Hisateru Tanaka <tanakahisateru@gmail.com>
 * @copyright  Copyright 2010-2011, Hisateru Tanaka <tanakahisateru@gmail.com>
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @version    0.5.0
 * @link       https://github.com/tanakahisateru/pinoco
 * @filesource
 * @package    Pinoco
 */

/**
 */
require_once dirname(__FILE__) . '/VarsList.php';

/**
 * Procedual varidation utility.
 * <code>
 * $validator = new Pinoco_Validator($data);
 * $validator->check('name')->is('not-empty')->is('max-length 255');
 * $validator->check('age')->is('not-empty')->is('integer')
 *                         ->is('>= 21', 'Adult only.');
 * if($validator->failed) {
 *   if($validator->result->name->valid) {
 *     echo $validator->result->name->message
 *   }
 *   if($validator->result->age->valid) {
 *     echo $validator->result->age->message
 *   }
 * }
 * </code>
 *
 * Builtin tests:
 *   pass, fail, empty, not-empty, max-length, min-length, in a,b,c, not-in a,b,c,
 *   numeric, integer, alpha, alpha-numeric, == n, != n, > n, >= n, < n,  <= n,
 *   match /regexp/, not-match /regexp/, email, url
 *
 * @package Pinoco
 * @property-read Pinoco_Vars $result;
 * @property-read Pinoco_Vars $errors;
 * @property-read boolean $succeeded;
 * @property-read boolean $failed;
 */
class Pinoco_Validator extends Pinoco_DynamicVars {

    private $_tests;
    private $_messages;
    private $_target;
    
    private $_result;
    private $_errors;

    /**
     * Constructor
     * @param string $target
     * @param string $message
     */
    public function __construct($target, $messages=array())
    {
        parent::__construct();
        
        $this->_tests = array();
        $this->_setupBuiltinTests();
        
        $this->_messages = array();
        $this->overrideErrorMessages($messages);
        
        $this->_target = $target;
        $this->_result = new Pinoco_Vars();
        $this->_errors = null;
    }
    
    private function _setupBuiltinTests()
    {
        // builtin testers
        $this->defineValidityTest('pass', array($this, '_testPass'),
            "Valid.");
        $this->defineValidityTest('fail', array($this, '_testFail'),
            "Invalid.");
        $this->defineValidityTest('empty', array($this, '_testEmpty'),
            "Leave as empty.");
        $this->defineValidityTest('not-empty', array($this, '_testNotEmpty'),
            "Reqierd.");
        $this->defineValidityTest('max-length', array($this, '_testMaxLength'),
            "In {0} letters.");
        $this->defineValidityTest('min-length', array($this, '_testMinLength'),
            "At least {0} letters.");
        $this->defineValidityTest('in', array($this, '_testIn'),
            "Coose in {0}.");
        $this->defineValidityTest('not-in', array($this, '_testNotIn'),
            "Choose else of {0}.");
        $this->defineValidityTest('numeric', array($this, '_testNumeric'),
            "By number.");
        $this->defineValidityTest('integer', array($this, '_testInteger'),
            "By integer number.");
        $this->defineValidityTest('alpha', array($this, '_testAlpha'),
            "Alphabet only.");
        $this->defineValidityTest('alpha-numeric', array($this, '_testAlphaNumeric'),
            "Alphabet or number.");
        $this->defineValidityTest('==', array($this, '_testEqual'),
            "Shuld equal to {0}.");
        $this->defineValidityTest('!=', array($this, '_testNotEqual'),
            "Should not equal to {0}.");
        $this->defineValidityTest('>', array($this, '_testGreaterThan'),
            "Greater than {0}.");
        $this->defineValidityTest('>=', array($this, '_testGreaterThanOrEqual'),
            "Greater than or equals to {0}.");
        $this->defineValidityTest('<', array($this, '_testLessorThan'),
            "Lessor than {0}.");
        $this->defineValidityTest('<=', array($this, '_testLessorThanOrEqual'),
            "Lessor than or equals to {0}.");
        $this->defineValidityTest('match', array($this, '_testMatch'),
            "Invalid pattern.");
        $this->defineValidityTest('not-match', array($this, '_testNotMatch'),
            "Not allowed pattern.");
        $this->defineValidityTest('email', array($this, '_testEmail'),
            "Email only.");
        $this->defineValidityTest('url', array($this, '_testUrl'),
            "URL only.");
    }
    
    /**
     * Defines custom test
     * @param string $testName
     * @param callable $callback
     * @param string $message
     * @return void
     */
    public function defineValidityTest($testName, $callback, $message)
    {
        $this->_tests[$testName] = array(
            'callback' => $callback,
            'message' => $message
        );
    }
    
    /**
     * Overrides error messages
     * @param array $messages
     * @return void
     */
    public function overrideErrorMessages($messages)
    {
        foreach($messages as $test=>$msg) {
            $this->_messages[$test] = $msg;
        }
    }
    
    /**
     * Resolve error message by test name.
     * @param string $testName
     * @return string
     */
    public function getMessageFor($testName)
    {
        if(isset($this->_messages[$testName])) {
            return $this->_messages[$testName];
        }
        else if(isset($this->_tests[$testName])) {
            return $this->_tests[$testName]['message'];
        }
        else {
            return 'not registered';
        }
    }
    
    /**
     * Executes validation test (called by varidation context).
     * @param string $field
     * @param string $testName
     * @param array $params
     * @return boolean
     */
    public function execValidityTest($field, $testName, $params)
    {
        $this->_errors = null;
        if(isset($this->_tests[$testName])) {
            $callback = $this->_tests[$testName]['callback'];
        }
        else {
            return false;
        }
        //type check
        if($this->_target instanceof Pinoco_Vars) {
            $exists = $this->_target->has($field);
            $value = $this->_target->get($field);
        }
        if($this->_target instanceof Pinoco_List) {
            $exists = intval($field) < $this->_target->count();
            $value = $exists ? $this->_target[$field] : null;
        }
        else if(is_array($this->_target)) {
            $exists = isset($this->_target[$field]);
            $value = $exists ? $this->_target[$field] : null;
        }
        else if(is_object($this->_target)) {
            $exists = isset($this->_target->$field);
            $value = $exists ? $this->_target->$field : null;
        }
        else {
            return false;
        }
        //main
        $args = $params;
        array_unshift($args, $value);
        array_unshift($args, $exists);
        array_unshift($args, $field);
        array_unshift($args, $this->_target);
        return call_user_func_array($callback, $args);
    }
    
    /**
     * Returns independent validation context.
     * @param string $name
     * @return Pinoco_ValidatorContext
     */
    public function contextFor($name)
    {
        return new Pinoco_ValidatorContext($this, $name);
    }
    
    /**
     * Starts named property check.
     * @param string $name
     * @return Pinoco_ValidatorContext
     */
    public function check($name)
    {
        $this->_errors = null;
        $this->_result->set($name, $this->contextFor($name));
        return $this->_result->get($name);
    }
    
    /**
     * Exports test all results.
     * @return Pinoco_Vars
     */
    public function get_result()
    {
        return $this->_result;
    }
    
    /**
     * Exports test results only failed.
     * @return Pinoco_Vars
     */
    public function get_errors()
    {
        if($this->_errors === null) {
            $this->_errors = new Pinoco_Vars();
            foreach($this->_result->keys() as $field) {
                $result = $this->_result->get($field);
                if($result->invalid) {
                    $this->_errors->set($field, $result);
                }
            }
        }
        return $this->_errors;
    }
    
    /**
     * Returns which all tests succeeded or not.
     * @return boolean
     */
    public function get_valid()
    {
        return ($this->get_errors()->keys()->count() == 0);
    }
    
    /**
     * Returns which validator has one or more failed tests.
     * @return boolean
     */
    public function get_invalid()
    {
        return ($this->get_errors()->keys() > 0);
    }
    
    /////////////////////////////////////////////////////////////////////
    // builtin tests
    private function _testPass($target, $name, $exists, $value)
    {
        return true;
    }
    private function _testFail($target, $name, $exists, $value)
    {
        return false;
    }
    private function _testEmpty($target, $name, $exists, $value)
    {
        if(!$exists || $value === null) { return true; }
        if($value === "0" || $value === 0 || $value === false) { return false; }
        return empty($value);
    }
    private function _testNotEmpty($target, $name, $exists, $value)
    {
        return !$this->_testEmpty($target, $name, $exists, $value);
    }
    private function _testMaxLength($target, $name, $exists, $value, $cond0=0)
    {
        return strlen(strval($value)) <= $cond0;
    }
    private function _testMinLength($target, $name, $exists, $value, $cond0=0)
    {
        return strlen(strval($value)) >= $cond0;
    }
    private function _testIn($target, $name, $exists, $value, $cond0='')
    {
        $as = explode(',', $cond0);
        foreach($as as $a) {
            if($value == $a) { return true; }
        }
        return false;
    }
    private function _testNotIn($target, $name, $exists, $value, $cond0='')
    {
        return !$this->_testIn($target, $name, $exists, $value, $cond0);
    }
    private function _testNumeric($target, $name, $exists, $value)
    {
        return is_numeric($value);
    }
    private function _testInteger($target, $name, $exists, $value)
    {
        return is_integer($value);
    }
    private function _testAlpha($target, $name, $exists, $value)
    {
        return ctype_alpha($value);
    }
    private function _testAlphaNumeric($target, $name, $exists, $value)
    {
        return ctype_alnum($value);
    }
    private function _testEqual($target, $name, $exists, $value, $cond0=null)
    {
        return $value == $cond0;
    }
    private function _testNotEqual($target, $name, $exists, $value, $cond0=null)
    {
        return !$this->_testEqual($target, $name, $exists, $value, $cond0);
    }
    private function _testGreaterThan($target, $name, $exists, $value, $cond0=0)
    {
        return $value > $cond0;
    }
    private function _testGreaterThanOrEqual($target, $name, $exists, $value, $cond0=0)
    {
        return $value >= $cond0;
    }
    private function _testLessorThan($target, $name, $exists, $value, $cond0=0)
    {
        return $value < $cond0;
    }
    private function _testLessorThanOrEqual($target, $name, $exists, $value, $cond0=0)
    {
        return $value <= $cond0;
    }
    private function _testMatch($target, $name, $exists, $value, $cond0='/^$/')
    {
        return preg_match($cond0, $value);
    }
    private function _testNotMatch($target, $name, $exists, $value, $cond0='/^$/')
    {
        return !$this->_testMatch($target, $name, $exists, $value, $cond0);
    }
    private function _testEmail($target, $name, $exists, $value)
    {
        return preg_match('/@[A-Z0-9][A-Z0-9_-]*(\.[A-Z0-9][A-Z0-9_-]*)*$/i', $value);
    }
    private function _testUrl($target, $name, $exists, $value)
    {
        return preg_match('/^[A-Z]+:\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)*):?(\d+)?\/?/i', $value);
    }
}

/**
 * @package Pinoco
 * @property-read string $test
 * @property-read boolean $valid
 * @property-read boolean $invalid
 * @property-read string $message
 */
class Pinoco_ValidatorContext extends Pinoco_DynamicVars {
    
    private $_validator;
    private $_name;
    
    private $_valid;
    private $_test;
    private $_message;
    
    /**
     * Constructor
     * @param Pinoco_Validator $validator
     * @param string $name
     */
    public function __construct($validator, $name)
    {
        parent::__construct();
        $this->_validator = $validator;
        $this->_name = $name;
        
        $this->_valid = true;
        $this->_test = null;
        $this->_message = null;
    }
    
    /**
     * Failed test.
     * @return string
     */
    public function get_test()
    {
        return $this->_test;
    }
    
    /**
     * is valid or not.
     * @return boolean
     */
    public function get_valid()
    {
        return $this->_valid;
    }
    
    /**
     * inverse of valid.
     * @return boolean
     */
    public function get_invalid()
    {
        return !$this->_valid;
    }

    /**
     * Error message for the first failed check.
     * @return string
     */
    public function get_message()
    {
        return $this->_message;
    }
    
    private function buildMessage($template, $params)
    {
        $target = array();
        $replacement = array();
        foreach($params as $k=>$v) {
            $target[] = '{'.$k.'}';
            $replacement[] = strval($v);
        }
        return str_replace($target, $replacement, $template);
    }
    
    private function _execute($test, $message=false)
    {
        $params = explode(' ', $test);
        $testName = array_shift($params);
        $result = $this->_validator->execValidityTest($this->_name, $testName, $params);
        if(!$result) {
            $this->_test = $test;
            $this->_valid = false;
            $template = $message ? $message : $this->_validator->getMessageFor($testName);
            $this->_message = $this->buildMessage($template, $params);
        }
    }
    
    /**
     * Check the field by specified test.
     * @param string $test
     * @param string $message
     * @return Pinoco_ValidatorContext
     */
    public function is($test, $message=false)
    {
        if($this->_valid) {
            $this->_execute($test, $message);
        }
        return $this;
    }
}
