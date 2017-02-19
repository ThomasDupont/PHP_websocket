<?php
namespace PHP_websocket;

use PHP_websocket\models\ModelExample;

/**
* @package PHP_websocket
* Main class of the web socket, override the method here to personnalise the action
*/
class WebSocket extends WebSocketServer {

    /**
    * MUST be implemented
    * @param Sockect $changedSocket the socket concern by the operation
    */
    public function operation ($changedSocket)
    {
        $tst_msg = $this->getInputStream();

        //<ACTION>
        $user_name = $tst_msg->name; //sender name
        $user_message = $tst_msg->message; //message text
        if($user_message == "salut") {
            $exampleModel = new ModelExample();
            $this->push([
                'type'=>'usermsg',
                'name'=> $exampleModel->getName(),
                'message'=>"coucou",
                'color'=>"F00"]
            );
        }
        if($user_message == "users") {
            $user_message = $this->getnbUsersConnected();
        }
        $user_color = $tst_msg->color; //color

        //</ACTION>
        $this->push([
            'type'=>'usermsg',
            'name'=>$user_name,
            'message'=>$user_message,
            'color'=>$user_color]
        );
    }

    /**
    * MUST be implemented
    * @param array $args (optional), The argument to push to the client
    */
    public function push(array $args = [])
    {
        $this->setOutputStream($args)
             ->pushToClient($this->getClients());
    }

    /**
    * @override
    * @param stdClass $args, contain ip of the user, and the concern socket
	* ['ip' => $ip, 'socket' => ['token' => $token, 'client'=> $socket]]
    */
    public function onConnect(\stdClass $args)
	{
        $this->setOutputStream(['type'=>'system', 'message'=>$args->ip.' connected'])
             ->pushToClient($args->socket);
	}

    /**
    * @override
    * @param stdClass $args, contain ip of the user, and the concern socket
	* ['ip' => $ip, 'socket' => ['token' => $token, 'client'=> $socket]]
    */
    function onDisconnect(\stdClass $args)
	{
        $this->setOutputStream(['type'=>'system', 'message'=>$args->ip.' disconnected'])
             ->pushToClient($args->socket);
	}

}
