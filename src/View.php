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

    protected $file, $viewDir, $template;

    public function __construct($file, $viewDir = null, $template = null)
    {
        $this->file = $file . self::TEMPLATES_EXTENSION;

        $baseDir = App::rootDir();

        if($viewDir) {
            $this->viewDir = $baseDir . DIRECTORY_SEPARATOR . $viewDir;
        }
        else {
            $this->viewDir = $baseDir . DIRECTORY_SEPARATOR . self::DEFAULT_VIEW_DIR;
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
        $viewContent = $this->viewDir . DIRECTORY_SEPARATOR . $this->file;
        ob_start();
        include $this->viewDir . DIRECTORY_SEPARATOR . $this->template;
        return ob_get_clean();
    }

    public function renderAjax($data = null) {
        if($data !== null && is_array($data)) {
            extract($data);
        }
        ob_start();
        include $this->viewDir . DIRECTORY_SEPARATOR . $this->file;
        return ob_get_clean();
    }
}