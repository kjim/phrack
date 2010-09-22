<?php
require_once('Phrack/Middleware.php');

class Phrack_Middleware_SimpleLogger extends Phrack_Middleware
{
    static public $LEVEL_NUMBERS;

    static public function _classinit()
    {
        $levelNumbers = array();
        $i = 0;
        foreach (array('debug', 'info', 'notice', 'warn', 'err', 'crit', 'alert', 'emerg') as $level) {
            $levelNumbers[$level] = $i++;
        }
        self::$LEVEL_NUMBERS = $levelNumbers;
    }

    public function call(&$environ)
    {
        $level = isset($this->args['level']) ? $this->args['level'] : 'debug';
        $min = isset(self::$LEVEL_NUMBERS[$level]) ? self::$LEVEL_NUMBERS[$level] : self::$LEVEL_NUMBERS['debug'];

        $environ['phsgix.logger'] = new Phrack_Middleware_SimpleLogger_Logger($environ['phsgi.errors'], $min);
        return $this->callApp($environ);
    }

    static public function wrap()
    {
        $args =& func_get_args();
        return parent::wrap(__CLASS__, $args);
    }
}
Phrack_Middleware_SimpleLogger::_classinit();

class Phrack_Middleware_SimpleLogger_Logger
{
    protected $io;
    protected $min;

    public function __construct($io, $minLevel)
    {
        $this->io = $io;
        $this->min = $minLevel;
    }

    public function debug($message)
    {
        $this->write($message, 'debug');
    }

    public function info($message)
    {
        $this->write($message, 'info');
    }

    public function notice($message)
    {
        $this->write($message, 'notice');
    }

    public function warn($message)
    {
        $this->write($message, 'warn');
    }

    public function err($message)
    {
        $this->write($message, 'err');
    }

    public function crit($message)
    {
        $this->write($message, 'crit');
    }

    public function alert($message)
    {
        $this->write($message, 'alert');
    }

    public function emerg($message)
    {
        $this->write($message, 'emerg');
    }

    protected function write($message, $level)
    {
        if (Phrack_Middleware_SimpleLogger::$LEVEL_NUMBERS[$level] >= $this->min) {
            fwrite($this->io, self::formatMessage($level, $message));
        }
    }

    static protected function formatTime($format, $time)
    {
        $oldlocale = setlocale(LC_ALL, null);
        setlocale(LC_ALL, 'en');
        $out = strftime($format, $time);
        setlocale(LC_ALL, $oldlocale);
        return $out;
    }

    static protected function formatMessage($level, $message)
    {
        $time = self::formatTime("%Y-%m-%dT%H:%M:%S", time());
        $level = strtoupper($level);
        return sprintf("[%s #%d] %s: %s\n",
                       $time,
                       posix_getpid(),
                       $level,
                       $message);
    }
}
