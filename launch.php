<?php

namespace PHP_websocket;

define("DEBUG", true);

ini_set('display_errors', DEBUG);
error_reporting(-DEBUG);

require_once "Autoloader.php";
Autoloader::register();

$socket = new WebSocket("localhost", 9001);
$socket->setMaxUsersConnected(2)
       ->setbufferOctets(1024) //the max size of buffer.
       ->setSocketTimeOut(10000)// in microseconds, depend the CPU capacity and your usage
       ->setAuthorizedDomains(["localhost", "example.com"]) // The origin domains
       ->setDomainForSocket("localhost") // the socket domain for the header
       ->setMemoryLimits(1024,4096) //set the log and max memory limit
       ->run();
