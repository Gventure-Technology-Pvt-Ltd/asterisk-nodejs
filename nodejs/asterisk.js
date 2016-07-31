var net = require('net');
var express = require('express');
var http = require('http');
var port = 5038;
var host = "127.0.0.1";
var username = "queue";
var password = "gv1234ue";
var CRLF = "\r\n";
var END = "\r\n\r\n";
var client = new net.Socket();
var app = express();
var server = http.createServer(app);
var io = require('socket.io').listen(server);
var json = require('json');
var blankcrm='{"channel":"", "lead_id":"","id_agent":null,"entry_date":null,"modify_date":"","status":"" ,"user":null, "vendor_lead_code":"","source_id":null,"list_id":"","gmt_offset_now":"","called_since_last_reset" :"","phone_code":"","phone_number":"","title":"","first_name":"","middle_initial":"","last_name" :"","address1":"","address2":"","address3":null,"city":"","state":"","province":"","postal_code":"","country_code":null,"gender":"","date_of_birth":null,"alt_phone":"","email":"","security_phrase":"","comments":"","called_count":"0","last_local_call_time":null,"rank":"0","owner":"","entry_list_id":"0","donation":"","transfromip":"","campaign_id":null,"record_type":"","crmid":0}';
server.listen(8000);

client.connect(port, host, function () {
    console.log('CONNECTED TO: ' + host + ':' + port);
    var obj = { Action: 'Login', Username: username, Secret: password};
    obj .ActionID =1;
    var socketData = generateSocketData(obj);
    console.log('DATA: ' + socketData);
    client.write(socketData, 'ascii');
});

generateSocketData = function(obj) {
    var str = '';
    for (var i in obj) {
        str += (i + ': ' + obj[i] + CRLF);
    }
    return str + CRLF;
};
/*client.on('data', function (data) {
	console.log(data.toString());
});*/
// routing
app.get('/', function (req, res) {
  res.sendfile(__dirname + '/index.html');
});


// usernames which are currently connected to the chat
var usernames = {};

// rooms which are currently available in chat
var rooms = ['103','104','105'];

io.sockets.on('connection', function (socket) {
	// when the client emits 'adduser', this listens and executes
	socket.on('adduser', function(username){
		socket.username = username;
		socket.room = username;
		usernames[username] = username;
		socket.join(username);
		socket.emit('updatechat', 'SERVER', 'you have connected to '+username);
		socket.broadcast.to(username).emit('updatechat', 'SERVER', username + ' has connected to this room');
		socket.emit('updaterooms', rooms, username);
	});
	
	// when the client emits 'sendchat', this listens and executes
	socket.on('pause', function (data) {
    		var obj = { Action: 'QueuePause', Interface: "SIP/"+socket.username, Paused: 'true'};
    		obj .ActionID =1;
    		var socketData = generateSocketData(obj);
		console.log(socketData);
    		client.write(socketData, 'ascii');
		client.on('data', function (ast_data) {
			io.sockets.in(socket.room).emit('updatechat', socket.username, ast_data.toString());
		});

		io.sockets.in(socket.room).emit('updatechat', socket.username, data);
	});
	
	socket.on('ready', function (data) {
    		var obj = { Action: 'QueuePause', Interface: "SIP/"+socket.username, Paused: 'false'};
    		obj .ActionID =1;
    		var socketData = generateSocketData(obj);
		console.log(socketData);
    		client.write(socketData, 'ascii');
		client.on('data', function (ast_data) {
			io.sockets.in(socket.room).emit('updatechat', socket.username, ast_data.toString());
		});

		io.sockets.in(socket.room).emit('updatechat', socket.username, data);
	});
	
	socket.on('hangup', function (data) {
    		var obj = { Action: 'Hangup', Channel: data};
    		obj .ActionID =1;
    		var socketData = generateSocketData(obj);
		console.log(socketData);
    		client.write(socketData, 'ascii');
		client.on('data', function (ast_data) {
			io.sockets.in(socket.room).emit('updatechat', socket.username, ast_data.toString());
		});

		io.sockets.in(socket.room).emit('updatechat', socket.username, data);
		io.sockets.in(socket.room).emit('crm', blankcrm);
	});
	
	socket.on('astcrm', function(data) {
		var crm = JSON.parse(data);
		console.log("Agent ["+crm.agent+"]"+data);
		socket.join(crm.agent);
		socket.channel=crm.channel;
		io.sockets.in(crm.agent).emit('crm', data);
		//io.sockets.in(data.agent).emit('updatechat', data.agent, data);
	});

	// when the user disconnects.. perform this
	socket.on('disconnect', function(){
		client.on('close', function () {
    			console.log('Connection closed');
		});
		delete usernames[socket.username];
		io.sockets.emit('updateusers', usernames);
		socket.broadcast.emit('updatechat', 'SERVER', socket.username + ' has disconnected');
		socket.leave(socket.room);
	});
});
