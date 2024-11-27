<?php

namespace App\Application;

class Application
{
    private static Application $instance;

    public static function getInstance(): Application
    {
        if (!isset(self::$instance)) {
            self::$instance = new Application();
        }

        return self::$instance;
    }

    public static function run()
    {
        $router = Router::getInstance();
        $router->resolve();
    }
}