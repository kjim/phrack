<?php
require_once('Phrack/Middleware.php');
require_once('Phrack/HTTP/Status.php');
require_once('Phrack/Util.php');
require_once('Phrack/MIME.php');

class Phrack_Middleware_ErrorDocument extends Phrack_Middleware
{
    public function call(&$environ)
    {
        $res = $this->callApp($environ);
        return $this->responseCb($res, array($this, 'process'), array(&$environ));
    }

    public function process(&$res, &$environ)
    {
        $status = (int) $res[0];
        if ( !(Phrack_HTTP_Status::isError($status) && isset($this->args[$status])) ) {
            return;
        }

        $path = $this->args[$status];
        if (isset($this->args['subrequest']) && $this->args['subrequest']) {
            foreach (array_keys($environ) as $k) {
                if (strpos($k, 'phsgi') !== 0) {
                    $environ['phsgi.errordocument.' . $k] = $environ[$k];
                }
            }

            // TODO: What if SCRIPT_NAME is not empty?
            $environ['REQUEST_METHOD'] = 'GET';
            $environ['REQUEST_URI']    = $path;
            $environ['PATH_INFO']      = $path;
            $environ['QUERY_STRING']   = '';
            unset($environ['CONTENT_LENGTH']);

            $subres = $this->callApp($environ);
            if ((int) $subres[0] === 200) {
                $res[1] = $subres[1];
                $res[2] = $subres[2];
            }
        }
        else {
            if (!is_readable($path)) {
                throw new Exception("$path: not readable file path");
            }
            $res[2] = fopen($path, 'rb');
            $h = Phrack_Util::headers($res[1]);
            $h->set('Content-Type', Phrack_MIME::mimetype($path));
        }
    }

    static public function wrap()
    {
        $args =& func_get_args();
        return parent::wrap(__CLASS__, $args);
    }
}
