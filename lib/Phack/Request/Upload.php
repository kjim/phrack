<?php

class Phack_Request_Upload
{
    protected $tempname;
    protected $size;
    protected $filename;
    protected $basename;
    protected $error;

    public function __construct($tempname, $size, $filename, $error)
    {
        if (!ini_get('file_uploads')) {
            throw new FileException(sprintf('Unable to create UploadedFile because "file_uploads" is disabled in your php.ini file (%s)', get_cfg_var('cfg_file_path')));
        }

        if ($error === null) {
            $error = UPLOAD_ERR_OK;
        }

        $this->tempname = $tempname;
        $this->size = $size;
        $this->filename = $filename;
        $this->error = $error;
    }

    public function getFileName()
    {
        return $this->filename;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function getTempName()
    {
        return $this->tempname;
    }

    public function getPath()
    {
        return $this->tempname;
    }

    public function getBaseName()
    {
        if (!$this->basename) {
            $this->basename = basename($this->filename);
        }
        return $this->basename;
    }

    public function hasError()
    {
        return $this->error !== UPLOAD_ERR_OK;
    }
}
