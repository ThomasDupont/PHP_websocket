<?php

namespace php_websocket\models;

interface ModelInterface {
    /**
    * @param WebSocket $webSocket, instance of WebSocket class
    * @package php_websocket
    * MUST be implemented
    * To use the WebSocket method inside the models
    */
    public function addInstance(php_websocket\WebSocket $webSocket);

}
