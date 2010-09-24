<?php
require_once('Phrack/HTTP/Status.php');

class Phrack_HTTP_Exception extends Exception
{
    protected $status;
    protected $statusMessage;

    public function __construct($status)
    {
        $this->status = $status;
        $this->statusMessage = Phrack_HTTP_Status::statusMessage($status);
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getStatusMessage()
    {
        return $this->statusMessage;
    }

    public function setStatusMessage($msg)
    {
        $this->statusMessage = $msg;
    }

    public function __toString()
    {
        return $this->status . ' ' . $this->statusMessage;
    }

    static public function create(/* $status, $args... */)
    {
        $args = func_get_args();
        $status = array_shift($args);
        if (Phrack_HTTP_Status::isInfo($status)) {
            return new Phrack_HTTP_Exception_1XX($status);
        }
        else if (Phrack_HTTP_Status::isSuccess($status)) {
            return new Phrack_HTTP_Exception_2XX($status);
        }
        else if (Phrack_HTTP_Status::isRedirect($status)) {
            $location = array_shift($args);
            return new Phrack_HTTP_Exception_3XX($status, $location);
        }
        else if (Phrack_HTTP_Status::isClientError($status)) {
            return new Phrack_HTTP_Exception_4XX($status);
        }
        else if (Phrack_HTTP_Status::isServerError($status)) {
            return new Phrack_HTTP_Exception_5XX($status);
        }
    }
}

class Phrack_HTTP_Exception_1XX extends Phrack_HTTP_Exception
{ }

class Phrack_HTTP_Exception_2XX extends Phrack_HTTP_Exception
{ }

class Phrack_HTTP_Exception_3XX extends Phrack_HTTP_Exception
{
    protected $location;

    public function __construct($status, $location)
    {
        parent::__construct($status);
        $this->location = $location;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function __toString()
    {
        return parent::__toString() . '(Location, ' . $this->location . ')';
    }
}

class Phrack_HTTP_Exception_4XX extends Phrack_HTTP_Exception
{ }

class Phrack_HTTP_Exception_5XX extends Phrack_HTTP_Exception
{ }
