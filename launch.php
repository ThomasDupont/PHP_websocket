<?php

namespace php_websocket;

define("DEBUG", false);
define("LOGPATH", __DIR__."/log.txt");

ini_set('display_errors', DEBUG);
error_reporting(-DEBUG);

require_once "Autoloader.php";
Autoloader::register();

$socket = new WebSocket("192.168.222.53", 9001);
$socket->setMaxUsersConnected(2)
       ->setbufferOctets(1024) //the max size of buffer.
       ->setSocketTimeOut(10000)// in microseconds, depend the CPU capacity and your usage
       ->setAuthorizedDomains(["localhost", "192.168.222.53"]) // The origin domains
       ->setDomainForSocket("192.168.222.53") // the socket domain for the header
       ->setMemoryLimits(1024,4096) //set the log and max memory limit
       ->run();
