<?php

namespace php_websocket\server;
/***********************************************************************************************
 * PHP WebSocket library
 * Copyright 2017 Thomas DUPONT, Vi Veri veniversum vivus vici
 * MIT License
 ************************************************************************************************/

abstract class WebSocketServer {

	/******************************
	* All variable MUST be private, to access it, please use accessor
	******************************/

	/**
	* @var string $host localhost by default
	*/
	private $_host = "localhost";

	/**
	* @var int $port default 9001
	*/
	private $_port = 9001;

	/**
	* @var string $domainForSocket domain of socket
	*/
	private $_domainForSocket = "localhost";

	/**
	* @var ressource $socket open socket
	*/
	private $_socket;

	/**
	* @var array $clients list of socket
	*/
	private $_clients = [];

	/**
	* @var stdClass $inputStream input from client
	*/
	private $_inputStream;

	/**
	* @var stdClass $outputStream output send to the client
	*/
	private $_outputStream;

	/**
	* @var int $socketTimeOut time out for the socket_select (microseconds)
	*/
	private $_socketTimeOut = 10000;

	/**
	* @var int $bufferOctets size of buffer in octets
	*/
	private $_bufferOctets = 1024;

	/**
	* @var bool $checkDomain activate the client domain checking
	*/
	private $_checkDomain = false;

	/**
	* @var array $authorizedDomains list of authorized domains
	*/
	private $_authorizedDomains = [];

	/**
	* @var array $limitUserConnection set to true to limit the number of connection
	*/
	private $_limitUserConnection = false;

	/**
	* @var array $maxUsersConnected total of connected users
	*/
	private $_maxUsersConnected = 100;

	/**
	* @var array $nbUsersConnected number of connected users
	*/
	private $_nbUsersConnected = 0;

	/**
	* @var int $memoryInit The initial php memory usage in octets
	*/
	private $_memoryInit = 0;

	/**
	* @var int $memoryUsage The current program memory usage in octets
	*/
	private $_memoryUsage = 0;

	/**
	* @var int $memoryLogLimit limit to log the overusage in kio
	*/
	private $_memoryLogLimit = 0;

	/**
	* @var int $memoryMaxLimit max usage of memory in kio
	*/
	private $_memoryMaxLimit = 0;

	/**
	* @param string $host
	* @param int $port
	*/
	public function __construct($host = "localhost", $port = 9001)
	{
		$this->_memoryInit = memory_get_usage();
		$this->_host = $host;
		$this->_port = $port;
		$this->_socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		$phpMemoryLimit = ((int) ini_get('memory_limit'))*1024*1024;

		$this->_memoryLogLimit = $phpMemoryLimit - 1;
		$this->_memoryMaxLimit = $phpMemoryLimit;

		socket_set_option($this->_socket, SOL_SOCKET, SO_REUSEADDR, 1);
		//bind socket to specified host
		socket_bind($this->_socket, 0, $port);
		//listen to port
		socket_listen($this->_socket);
		//create & add listning socket to the list
		$this->_clients[] = [
			'token' => md5(uniqid()),
			'client' => $this->_socket
		];
	}

	/**
	* Set the socket_select timeOut
	*/
	public function setSocketTimeOut($timeOut)
	{
		if(!is_int($timeOut)) {
			throw new \Exception("The given parametter $timeOut is not an integer", 500);
		}
		$this->_socketTimeOut = (int) $timeOut;
		return $this;
	}

	/**
	* Set the socket_select timeOut
	*/
	public function setbufferOctets($oct)
	{
		if(!is_int($oct)) {
			throw new \Exception("The given parametter $oct is not an integer", 500);
		}
		$this->_bufferOctets = (int) $oct;
		return $this;
	}

	/**
	* Set the number of connected user
	*/
	public function setMaxUsersConnected($max)
	{
		if(!is_int($max)) {
			throw new \Exception("The given parametter $max is not an integer", 500);
		}
		$this->_limitUserConnection = true;
		$this->_maxUsersConnected = (int) $max;
		return $this;
	}


	/**
	* Set the list of Authorized domains
	* @param array $domains
	* @return Instance
	*/
	public function setAuthorizedDomains (array $domains)
	{
		$this->_checkDomain = true;
		$this->_authorizedDomains = $domains;
		return $this;
	}

	/**
	* Set the domain view by the client for websocket
	* @param string $domain
	* @return Instance
	*/
	public function setDomainForSocket ($domain)
	{
		$this->_domainForSocket = $domain;
		return $this;
	}

	/**
	* Set limit usage for memory
	* @param int $logLimit limit for yellow zone
	* @param int $maxLimit limit for red zone
	* @return Instance
	*/
	public function setMemoryLimits ($logLimit, $maxLimit)
	{
		if(!is_int($logLimit) || !is_int($maxLimit)) {
			throw new \Exception("The given parametter $logLimit or $maxLimit is not an integer", 500);
		}
		$this->_memoryLogLimit = $logLimit;
		$this->_memoryMaxLimit = $maxLimit;
		return $this;
	}

	/**
	* operation between input and output (BD, algo...)
	* @param Socket $changedSocket the socket concerne by the operation
	* MUST be impemented
	*/
	abstract protected function operation ($changedSocket);

	/**
	* send a push notification to client
	* @param array $args, optionnal parametters
	* MUST be implemented
	*/
	abstract protected function push(array $args = []);

	/**
	* get the input stream of the server
	*/
	public function getInputStream()
	{
		return $this->_inputStream;
	}

	/**
	* get the number of connected user to the socket
	*/
	public function getnbUsersConnected()
	{
		return $this->_nbUsersConnected;
	}

	/**
	* get all clients
	*/
	public function getClients ()
	{
		return $this->_clients;
	}

	/**
	* Set the response
	*/
	public function setOutputStream (array $output)
	{
		$this->_outputStream = $output;
		return $this;
	}

	/**
	* send notification to client
	* @param array $clients, clients to send the notification
	*/
	public function pushToClient(array $clients)
	{
		$responseText = $this->_mask(json_encode($this->_outputStream));
		$arrLen = count($clients);
		for($i = 0; $i < $arrLen; $i++) {
			$this->_sendMessage($responseText, $clients[$i]['client']); //send data
		}
	}


	/**
	* Securization of the connections
	* @param int $secretKey The secret key using for authantification
	* @return bool
	* SHOULD be implemented
	*/
	protected function authSecurization($secretKey)
	{
		return true;
	}

	/**
	* Action on connect
	* @param stdClass $args, contain ip of the user, and the concern socket
	* ['ip' => $ip, 'socket' => ['token' => $token, 'client'=> $socket]]
	* SHOULD be implemented
	* By default, send a message
	*/
	protected function onConnect(\stdClass $args)
	{
		//prepare json data
		$response = $this->_mask(json_encode(
			['type'=>'system', 'message'=>$args->ip.' connected']
		));
		//notify user about new connection
		$this->_sendMessage($response, $args->socket[0]['client']);
	}

	/**
	* Action on disconnect
	* @param stdClass $args, contain ip of the user, and the concern socket
	* ['ip' => $ip, 'socket' => ['token' => $token, 'client'=> $socket]]
	* SHOULD be implemented
	* By default, send a message
	*/
	protected function onDisconnect(\stdClass $args)
	{
		$response = $this->_mask(json_encode(
			['type'=>'system', 'message'=>$args->ip.' disconnected']
		));
		$this->_sendMessage($response, $args->socket[0]['client']);
	}


	/**
	* Initialisation of the server
	*/
	public function run()
	{
		while (true) {

			//action for memory issue
			$this->_memoryUsage = memory_get_usage() - $this->_memoryInit;

			if(
				$this->_memoryUsage > $this->_memoryLogLimit*1024
				&& $this->_memoryUsage < $this->_memoryMaxLimit*1024
			) {
				$this->_echo(
					"\033[33m"
					.($this->_memoryUsage/1024)
					." ko used, ".$this->_memoryLogLimit
					." defined for yellow zone. "
					.$this->_memoryLogLimit." defined for red zone"
					."\033[0m",
					"ALERT"
				);
			} else if($this->_memoryUsage >= $this->_memoryMaxLimit*1024) {
				$this->_echo(
					"\033[31m"
					.($this->_memoryUsage/1024)
					." ko used, ".$this->_memoryMaxLimit
					." defined for red zone"
					."\033[0m",
					"ERROR"
				);
				sleep(60);
				continue;
			}

			//manage multipal connections
			$changed = array_column($this->_clients, 'client');
			//returns the socket resources in $changed array
			$null = NULL;
			socket_select($changed, $null, $null, 0, $this->_socketTimeOut);

			unset($null);
			//check for new socket
			if (in_array($this->_socket, $changed)) {
				$socketNew = socket_accept($this->_socket); //accpet new socket

				$newClient = [
					'token' => md5(uniqid()),
					'client' => $socketNew
				];
				$this->_clients[] = $newClient; //add socket to client array

				$header = socket_read($socketNew, $this->_bufferOctets); //read data sent by the socket

				if($this->_checkDomain && !$this->_checkDomain($header)) {
					 continue;
				}

				$this->_performHandshaking($header, $socketNew); //perform websocket handshake

				$this->_nbUsersConnected++;
				if(
					$this->_nbUsersConnected > $this->_maxUsersConnected
					&& $this->_limitUserConnection
				) {
					$msg = "Number of connected users is over limit";
					$response = $this->_mask(json_encode(
						['type'=>'system', 'message'=> $msg]
					));
					$this->_sendMessage($response, $socketNew);
					$this->_log($msg, true);
					continue;
				}

				socket_getpeername($socketNew, $ip); //get ip address of connected socket

				$this->_echo("\033[32m New connection from $ip, ".$this->_nbUsersConnected." connected.\033[0m");

				$this->onConnect(
					(object) ['socket' => [$newClient], 'ip' => $ip]
				);

				//make room for new socket
				$foundSocket = array_search($this->_socket, $changed);
				unset($changed[$foundSocket]);
				unset($socketNew);
			}

			//loop through all connected sockets
			foreach ($changed as $changedSocket) {

				//check for any incomming data
				while(socket_recv($changedSocket, $buff, $this->_bufferOctets, 0) >= 1)
				{
					$receivedText = $this->_unmask($buff); //unmask data
					$this->_inputStream = json_decode($receivedText); //json decode
					//For the other event without message
					if(!empty($this->_inputStream)) {
						if(!$this->authSecurization("")) {
							continue;
						}
						//operation with the server
						$this->operation($changedSocket);
					}

					break 2;
				}

				unset($buff);

				$buf = socket_read($changedSocket, $this->_bufferOctets, PHP_NORMAL_READ);
				if ($buf === false) { // check disconnected client
					// remove client for $clients array
					$foundSocket = array_search($changedSocket, array_column($this->_clients, 'client'));
					socket_getpeername($changedSocket, $ip);

					//decrease the connection cunter
					$this->_nbUsersConnected--;
					$this->_echo("\033[32m Remove connection from $ip, ".$this->_nbUsersConnected." connected.\033[0m");
					//Use the onDisconnect function
					$this->onDisconnect(
						(object) ['socket' => [$this->_clients[$foundSocket]], 'ip' => $ip]
					);
					unset($this->_clients[$foundSocket]);

				}

				unset($buf);

			}
			unset($changed);
		}
	}

	/**
	* send the message to client
	* @param string $msg final message
	* @param Socket $changedSocket the users socket
	* @return bool
	*/
	private function _sendMessage($msg, $changedSocket = "")
	{
		//var_dump($changedSocket);
		if(empty($changedSocket)) {
			foreach($this->_clients as $changedSocket)
			{
				if(!socket_write($changedSocket,$msg,strlen($msg))) {
					$error = socket_last_error();
					$errorCode = socket_strerror($error);
					$this->_log("Error in socket writting $error, code $errorCode", true);
				}
			}
			return true;
		} else if (socket_write($changedSocket,$msg,strlen($msg))) {
			return true;
		} else {
			$error = socket_last_error();
			$errorCode = socket_strerror($error);
			$this->_log("Error in socket writting $error, code $errorCode", true);
			return false;
		}

	}

	/**
	* Check if the user come from a right domain (be carefull, could be bypass by curl)
	* @param string $header, the header send by ws://
	* @return bool
	*/
	private function _checkDomain($header)
	{
		$hder = explode("\n", $header);
		$domain = "";
		foreach($hder as $items) {
			$items = str_replace("https://","", $items);
			$items = str_replace("http://","", $items);
			$a = explode(":", $items);
			if($a[0] == "Origin"){
				$domain = trim($a[1]);
			}
		}
		if(!in_array($domain, $this->_authorizedDomains)) {
			$this->_log($domain." Request from wrong domain", true);
			return false;
		}
		return true;
	}

	/**
	* Decode incoming framed message
	* @param string $text, the clear data
	* @return string $text, encode data
	*/
	private function _unmask($text)
	{
		$length = ord($text[1]) & 127;
		if($length == 126) {
			$masks = substr($text, 4, 4);
			$data = substr($text, 8);
		} else if($length == 127) {
			$masks = substr($text, 10, 4);
			$data = substr($text, 14);
		} else {
			$masks = substr($text, 2, 4);
			$data = substr($text, 6);
		}
		$text = "";
		$len = strlen($data);
		for ($i = 0; $i < $len; ++$i) {
			$text .= $data[$i] ^ $masks[$i%4];
		}
		return $text;
	}

	/**
	* Encode message for transfer to client.
	* @param string $text, the encode data
	* @return string $text, clear data
	*/
	private function _mask($text)
	{
		$b1 = 0x80 | (0x1 & 0x0f);
		$length = strlen($text);

		if($length <= 125) {
			$header = pack('CC', $b1, $length);
		} else if ($length > 125 && $length < 65536) {
			$header = pack('CCn', $b1, 126, $length);
		} else if ($length >= 65536) {
			$header = pack('CCNN', $b1, 127, $length);
		}
		return $header.$text;
	}

	/**
	* handshake new client.
	* @param string $receivedHeader, header received from new connection asking
	* @param Socket $clientConn, the new ressource
	*/
	private function _performHandshaking($recevedHeader,$clientConn)
	{
		$headers = [];
		$lines = preg_split("/\r\n/", $recevedHeader);
		foreach($lines as $line)
		{
			$line = chop($line);
			if(preg_match('/\A(\S+): (.*)\z/', $line, $matches))
			{
				$headers[$matches[1]] = $matches[2];
			}
		}
		$host = $this->_domainForSocket;
		$port = $this->_port;
		$secKey = $headers['Sec-WebSocket-Key'];
		$secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
		//hand shaking header
		$upgrade  = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
			"Upgrade: websocket\r\n" .
			"Connection: Upgrade\r\n" .
			"WebSocket-Origin: $host\r\n" .
			"WebSocket-Location: ws://$host:$port\r\n".
			"Sec-WebSocket-Accept:$secAccept\r\n\r\n";
		socket_write($clientConn, $upgrade, strlen($upgrade));
	}

	/**
	* Log error to a special file
	* @param string $msg Message to log
	* @param bool $echo, if true, write the message in the console
	*/
	private function _log($msg, $echo = false)
	{
		if(DEBUG) {
			$this->_echo($msg);
			file_put_contents(LOGPATH, $msg."\n", FILE_APPEND);
		} else if ($echo) {
			$this->_echo($msg);
		}
	}

	/**
	* Echo informations to the console
	* @param string $msg Message to echo
	* @param string $type, default INFO, value : ALERT, ERROR
	*/
	private function _echo($msg, $type = "INFO")
	{
		echo date(DATE_RFC2822).": [$type] : $msg \n";
	}

}
