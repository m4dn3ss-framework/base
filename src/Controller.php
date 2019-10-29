<?php

namespace m4dn3ss\framework;


class Controller
{

    /**
     * @param $file
     * @param null $data
     * @param bool $ajax
     * @throws \Exception
     */
    public function render($file, $data = null, $ajax = false)
    {
        $view = new View($file, App::config()->getParam('views:viewsDirectory'), App::config()->getParam('views:layoutFile'), App::config()->getParam('publicFolderOutside'));
        if ($ajax) {
            echo $view->renderAjax($data);
        } else {
            echo $view->render($data);
        }
    }

    /**
     * @param $data
     */
    public function jsonResponse($data)
    {
        echo json_encode([
            'success' => 0
        ]);
        exit;
    }

    /**
     * @param $file
     */
    public function sendFile($file)
    {
        header("Content-type: " . mime_content_type($file));
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        readfile($file);
        exit;
    }
}