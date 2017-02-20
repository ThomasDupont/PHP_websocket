<?php

namespace php_websocket\models;

/**
* @package PHP_websocket
* A simple example of a model could be use by the websocket
*/
final class ModelExample implements ModelInterface {

    private $name;
    private $socket;

    /**
    * @param WebSocket $webSocket, instance of WebSocket class
    * @package php_websocket
    * MUST be implemented
    * To use the WebSocket method inside the models
    */
    public function addInstance(php_websocket\WebSocket $webSocket)
    {
        $this->socket = $webSocket;
    }

    public function __construct ()
    {
        $this->name = "toto";
    }

    public function getName()
    {
        return $this->name;
    }
}
