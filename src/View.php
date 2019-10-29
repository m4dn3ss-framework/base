<?php

namespace m4dn3ss\framework;

/**
 * Class View
 * @package m4dn3ss\framework
 * @author Viacheslav Zhabonos - vyacheslav0310@gmail.com
 */

class View
{
    const DEFAULT_VIEW_DIR = 'views';
    const DEFAULT_TEMPLATE = 'layout';

    const TEMPLATES_EXTENSION = '.php';

    protected $file, $viewDir, $template, $publicFolderOutside;

    public function __construct($file, $viewDir = null, $template = null, $publicFolderOutside = true)
    {
        $this->file = $file . self::TEMPLATES_EXTENSION;
        $this->publicFolderOutside = $publicFolderOutside;

        $baseDir = $_SERVER['DOCUMENT_ROOT'];
        if($this->publicFolderOutside)
            $baseDir = $_SERVER['DOCUMENT_ROOT'] . DS . '..';

        if($viewDir) {
            $this->viewDir = $baseDir . DS . $viewDir;
        }
        else {
            $this->viewDir = $baseDir . DS . self::DEFAULT_VIEW_DIR;
        }

        if($template) {
            $this->template = $template . self::TEMPLATES_EXTENSION;
        }
        else {
            $this->template = self::DEFAULT_TEMPLATE . self::TEMPLATES_EXTENSION;
        }
    }

    public function render($data = null) {
        if($data !== null && is_array($data)) {
            extract($data);
        }
        $viewContent = $this->viewDir . DS . $this->file;
        ob_start();
        include $this->viewDir . DS . $this->template;
        return ob_get_clean();
    }

    public function renderAjax($data = null) {
        if($data !== null && is_array($data)) {
            extract($data);
        }
        ob_start();
        include $this->viewDir . DS . $this->file;
        return ob_get_clean();
    }
}