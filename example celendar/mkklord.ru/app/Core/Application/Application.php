<?php

namespace App\Core\Application;

use App\Core\Application\Container\Container;
use App\Core\Application\Response\Response;
use App\Core\Application\Traits\Singleton;

class Application extends Container {
    use Singleton;

    private Container $container;

    private function __construct()
    {
        $this->container = new Container();
        $this->callServiceProviders();
    }

    /**
     * @throws \Exception
     */
    public function run()
    {
        try {
            $router = $this->container->make('Router');

            $response = $router->dispatch();

            if ($response instanceof Response) {
                $response->send();
            } else {
                throw new \Exception('Response is not instance of Response');
            }

        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }

    private function callServiceProviders()
    {
        $providers = config('app.providers');

        foreach ($providers as $provider) {
            $providerInstance = new $provider($this->container);
            $providerInstance->register();
            $providerInstance->boot();
        }
    }

    public static function getInstance(): Application
    {
        return self::singleton();
    }

    private function handleException(\Throwable $e) {
        error_log($e->getMessage());

        Response::json([
            'message' => 'Ошибка',
        ], 500)->send();
    }

    public function make(string $abstract)
    {
        return $this->container->make($abstract);
    }
}