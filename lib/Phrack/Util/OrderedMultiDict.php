<?php
require_once('Phrack/Util/MultiDict.php');

class Phrack_Util_OrderedMultiDict extends Phrack_Util_MultiDict
{
    protected $keyorder = array();

    public function addAll($key, $values)
    {
        parent::addAll($key, $values);
        $keys = array_pad(array(), count($values), $key);
        array_splice($this->keyorder, count($this->keyorder), count($keys), $keys);
    }

    public function remove($key)
    {
        parent::remove($key);
        $this->keyorder = array_merge(
            array_filter($this->keyorder, create_function('$k', "return \$k !== '$key';")));
    }

    public function items()
    {
        $data = $this->data; // copy

        $pairs = array();
        foreach ($this->keyorder as $key) {
            $val =& $data[$key];
            $pairs[] = array($key, array_shift($val));
        }
        return $pairs;
    }

    public function keys()
    {
        return $this->keyorder;
    }

    public function values()
    {
        $data = $this->data;

        $values = array();
        foreach ($this->keyorder as $key) {
            $val =& $data[$key];
            $values[] = array_shift($val);
        }
        return $values;
    }
}
