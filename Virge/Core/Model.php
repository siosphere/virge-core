<?php

namespace Virge\Core;

/**
 * 
 * @author Michael Kramer
 */
class Model {
    
    /**
     * Construct our object, will assign properties in key => value
     * @param array $data
     */
    public function __construct($data = array()) {
        
        if(!is_array($data)){
            return;
        }
        
        foreach ($data as $key => $value) {
            if (is_string($key) && $key != '') {
                $this->$key = $value;
            }
        }
    }
    
    /**
     * Magic getter/setters
     * @param string $method
     * @param string $args
     */
    public function __call($method, $args) {
        
        //if an actual method, call that instead
        if (isset($this->$method) === true) {
            $func = $this->$method;
            return $func();
        }
        
        //determine what type of method it is
        //GET
        $temp = explode('get', $method);

        if ($temp[0] == '' && isset($temp[1])) {
            unset($temp[0]);
            return $this->_getValue($temp);
        }

        $temp = explode('set', $method);

        if (substr_count($method, 'set') > 1) {
            $field = substr($method, 2, strlen($method));
        } else if (isset($temp[1])) {
            $field = $temp[1];
        }

        if (isset($field)) {
            return $this->_setValue($field, $args[0]);
        }

        //add to an array
        $temp = explode('add', $method);
        unset($field);
        if (substr_count($method, 'add') > 1) {
            $field = substr($method, 2, strlen($method));
        } else if (isset($temp[1])) {
            $field = $temp[1];
        }

        if (isset($field)) {
            return $this->_addToArray($field, $args[0]);
        }

        //remove from an array
        $temp = explode('remove', $method);
        unset($field);
        if (substr_count($method, 'remove') > 1) {
            $field = substr($method, 2, strlen($method));
        } else if (isset($temp[1])) {
            $field = $temp[1];
        }

        if (isset($field)) {
            return $this->_removeFromArray($field, $args[0]);
        }
    }
    
    /**
     * Get value from our methodData
     * @param array $methodData
     * @return mixed
     */
    protected function _getValue($methodData) {
        $variable = implode('get', $methodData);
        //we are indeed a get method
        //see if we need to split again
        preg_match_all('/[A-Z][^A-Z]*/',$variable, $results);
        $string = $results[0];
        $i = 0;
        $v = '';
        
        foreach ($string as $str) {
            if ($i > 0) {
                $v .='_';
            }
            $v .= strtolower($str);
            $i++;
        }

        if (isset($this->$v)) {
            return $this->$v;
        }
        
        return NULL;
    }
    
    /**
     * 
     * @param string $field
     * @param mixed $value
     * @return \Virge\Core\Model\Model
     */
    protected function _setValue($field, $value) {
        //see if we need to split again
        preg_match_all('/[A-Z][^A-Z]*/', $field, $results);
        $string = $results[0];
        $i = 0;
        $v = '';
        
        foreach ($string as $str) {
            if ($i > 0) {
                $v .='_';
            }
            $v .= strtolower($str);
            $i++;
        }
        
        $this->$v = $value;
        return $this;
    }
    
    /**
     * Adds a value to a field
     * @param string $field
     * @param mixed $value
     * @return \Virge\Core\Model\Model
     */
    protected function _addToArray($field, $value) {
        //make field plural, not always perfect, but meh
        $field .= 's';
        //see if we need to split again
        preg_match_all('/[A-Z][^A-Z]*/', $field, $results);
        $string = $results[0];
        $i = 0;
        $v = '';
        
        foreach ($string as $str) {
            if ($i > 0) {
                $v .='_';
            }
            $v .= strtolower($str);
            $i++;
        }
        
        if (!is_array($this->$v)) {
            $currentValues = explode(',', $this->$v);
        } else {
            $currentValues = $this->$v;
        }
        
        if (!is_array($currentValues)) {
            $currentValues = array();
        }
        
        $tempArray = array();

        $tempArray[] = $value;
        $this->$v = array_unique(array_merge($currentValues, $tempArray));
        return $this;
    }
    
    /**
     * Removes a value from a field
     * @param string $field
     * @param mixed $value
     * @return \Virge\Core\Model\Model
     */
    protected function _removeFromArray($field, $value) {
        //make field plural, not always perfect, but meh
        $field .= 's';
        //see if we need to split again
        preg_match_all('/[A-Z][^A-Z]*/', $field, $results);
        $string = $results[0];
        $i = 0;
        $v = '';
        
        foreach ($string as $str) {
            if ($i > 0) {
                $v .='_';
            }
            $v .= strtolower($str);
            $i++;
        }
        
        $currentValues = explode(',', $this->$v);
        
        foreach ($currentValues as $key => $value) {
            if ($value == $value) {
                unset($currentValues[$key]);
            }
        }
        
        $this->$v = $currentValues;
        
        return $this;
    }
    
    /**
     * Set value ( will call setter )
     * @param string $key
     * @param mixed $value
     * @return boolean
     * @throws \InvalidArgumentException
     */
    public function set($key, $value){
        if(!is_string($key)){
            throw new \InvalidArgumentException("key must be a string");
        }
        
        if(trim($key) === ''){
            return false;
        }
        
        //build method call
        $temp = explode('_', $key);
        $method = 'set';
        foreach ($temp as $t) {
            $method .= ucfirst($t);
        }
        
        if(method_exists($this, $method)){
            return call_user_func_array(array($this, $method), array());
        }
        
        return $this->{$key} = $value;
    }

    /**
     * Get value (will call getter )
     * @param string $key
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function get($key) {
        if(!is_string($key)){
            throw new \InvalidArgumentException("key must be a string");
        }
        
        return isset($this->$key) ? $this->$key : null;
    }
}