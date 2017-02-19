<?php

namespace PHP_websocket\models;

use PHP_websocket\WebSocket;
/**
* @package PHP_websocket
* A simple example of a model could be use by the websocket
*/
final class ModelExample {

    private $name;
    private $socket;

    public function __construct ()
    {
        $this->name = "toto";
    }

    public function getName()
    {
        return $this->name;
    }
}
