<?php

class Phack_Util_MultiDict implements ArrayAccess
{
    protected $data;

    public function __construct(array $multi = array())
    {
        $this->clear();
        $this->merge($multi);
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

    public function add(/* $key, $value[, $value ...] */)
    {
        $values = func_get_args();
        $this->addAll(array_shift($values), $values);
    }

    public function addAll($key, $values)
    {
        if (isset($this->data[$key])) {
            array_splice($this->data[$key], count($this->data[$key]), count($values), $values);
        }
        else {
            $this->data[$key] = $values;
        }
    }

    public function merge(array $multi)
    {
        foreach ($multi as $entry) {
            $this->add($entry[0], $entry[1]);
        }
    }

    public function mergeMixedDict(array $dict)
    {
        $callback = array($this, 'addAll');
        foreach ($dict as $key => $value) {
            call_user_func_array($callback, array($key, is_array($value) ? $value : array($value)));
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
