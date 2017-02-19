<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="utf-8">
    <title>Chat example</title>
    <style type="text/css">

    .panel{


    margin-right: 3px;
    }

    .button {
        background-color: #4CAF50;
        border: none;
        color: white;
    	margin-right: 30%;
    	margin-left: 30%;
        text-decoration: none;
        display: block;
        font-size: 16px;
        cursor: pointer;
    	width:30%;
        height:40px;
    	margin-top: 5px;

    }
    input[type=text]{
    		width:100%;
    		margin-top:5px;

    	}


    .chat_wrapper {
    	width: 70%;
    	height:472px;
    	margin-right: auto;
    	margin-left: auto;
    	background: #3B5998;
    	border: 1px solid #999999;
    	padding: 10px;
    	font: 14px 'lucida grande',tahoma,verdana,arial,sans-serif;
    }
    .chat_wrapper .message_box {
    	background: #F7F7F7;
    	height:350px;
    		overflow: auto;
    	padding: 10px 10px 20px 10px;
    	border: 1px solid #999999;
    }
    .chat_wrapper  input{
    	//padding: 2px 2px 2px 5px;
    }
    .system_msg{color: #BDBDBD;font-style: italic;}
    .user_name{font-weight:bold;}
    .user_message{color: #88B6E0;}

    @media only screen and (max-width: 720px) {
        /* For mobile phones: */
        .chat_wrapper {
            width: 95%;
    	height: 40%;
    	}


    	.button{ width:100%;
    	margin-right:auto;
    	margin-left:auto;
    	height:40px;}

    }

    </style>
</head>
<body>

    <div class="chat_wrapper">
        <div class="message_box" id="message_box"></div>

        <div class="panel">
            <input type="text" name="name" id="name" placeholder="Your Name" maxlength="15" />

            <input type="text" name="message" id="message" placeholder="Message" maxlength="80"
            onkeydown = "if (event.keyCode == 13)document.getElementById('send-btn').click()"  />

        </div>

        <button id="send-btn" class=button>Send</button>

    </div>

    <script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
    <script language="javascript" type="text/javascript">
    $(document).ready(function(){
    	//create a new WebSocket object.
        var colours = ['007AFF','FF7000','FF7000','15E25F','CFC700','CFC700','CF1100','CF00BE','F00','000'];

    	websocket = new WebSocket("ws://localhost:9001");

    	websocket.onopen = function(ev) { // connection is open
    		$('#message_box').append("<div class=\"system_msg\">Connected!</div>"); //notify user
    	}

    	$('#send-btn').click(function(){ //use clicks message send button
    		var mymessage = $('#message').val(); //get message text
    		var myname = $('#name').val(); //get user name

    		if(myname == ""){ //empty name?
    			alert("Enter your Name please!");
    			return;
    		} else if(mymessage == ""){ //emtpy message?
    			alert("Enter Some message Please!");
    			return;
    		}
    		$("#name").css('visibility', "hidden");

    		var objDiv = document.getElementById("message_box");
    		objDiv.scrollTop = objDiv.scrollHeight;
    		//prepare json data
    		var msg = {
    		    message: mymessage,
    		    name: myname,
    		    color : colours[parseInt(Math.random()*10-1)]
    		};
    		//convert and send data to server
    		websocket.send(JSON.stringify(msg));
    	});

    	//#### Message received from server?
    	websocket.onmessage = function(ev) {
    		var msg = JSON.parse(ev.data), //PHP sends Json data
    		    type = msg.type, //message type
    		    umsg = msg.message, //message text
    		    uname = msg.name, //user name
    		    ucolor = msg.color; //color

    		if(type == 'usermsg') {
    			$('#message_box').append("<div><span class=\"user_name\" style=\"color:#"+ucolor+"\">"+uname+"</span> : <span class=\"user_message\">"+umsg+"</span></div>");
    		} else if(type == 'system') {
    			$('#message_box').append("<div class=\"system_msg\">"+umsg+"</div>");
    		}

    		$('#message').val(''); //reset text

    		var objDiv = document.getElementById("message_box");
    		objDiv.scrollTop = objDiv.scrollHeight;
    	};

    	websocket.onerror= function(ev){$('#message_box').append("<div class=\"system_error\">Error Occurred - "+ev.data+"</div>");};
    	websocket.onclose= function(ev){$('#message_box').append("<div class=\"system_msg\">Connection Closed</div>");};
    });

    </script>
</body>
</html>
