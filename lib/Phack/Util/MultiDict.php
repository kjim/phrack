<?php

class Phack_Util_MultiDict implements ArrayAccess
{
    protected $data;

    public function __construct(array $data = array())
    {
        $this->clear();
        $this->merge($data);
    }

    public function get($key, $default = null)
    {
        $values = $this->getvalues($key);
        if (!empty($values)) {
            return $values[count($values)-1];
        }
        return null;
    }

    public function getAll($key, $default = array())
    {
        return $this->getvalues($key);
    }

    public function keys()
    {
        return array_keys($this->data);
    }

    public function add($key, $value)
    {
        if (isset($this->data[$key])) {
            $this->data[$key][] = $value;
        }
        else {
            $this->data[$key] = array($value);
        }
    }

    public function merge(array $data)
    {
        foreach ($data as $entry) {
            $this->add($entry[0], $entry[1]);
        }
    }

    public function remove($key)
    {
        unset($this->data[$key]);
    }

    public function clear()
    {
        $this->data = array();
    }

    public function flatten()
    {
        $pairs = array();
        foreach ($this->data as $key => $values) {
            foreach ($values as $value) {
                $pairs[] = array($key, $value);
            }
        }
        return $pairs;
    }

    public function __toString()
    {
        return print_r($this->flatten(), true);
    }

    protected function getvalues($key, $default = array())
    {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }

    // ArrayAccess
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        $values = $this->data[$offset];
        return $values[count($values)-1];
    }

    public function offsetSet($offset, $value)
    {
        $this->add($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }
}
