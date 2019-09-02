var express = require('express'),
    app = express(),
    https =  require('http'),
    server = https.createServer(app),
    io = require('socket.io')(server),
    port = 3000,
    ipAddress = '0.0.0.0';


const user_ids = [];
const users = {};



server.listen(port,ipAddress,function () {
    console.log('Chat Server started on port : ' + port);
});


app.get('/', function (req, res) {
    res.sendFile(__dirname + '/package.json');
});



// =============================
// Connections
// =============================
io.on('connection', function(socket) {
    socket.on('send-chat-message', function(data) {
        io.emit('received-message'+data.id, {sender_id: data.sender_id, message: data.message});
    });
    socket.on('send-updated-message', function(data) {
        io.emit('updated-message'+data.id, {message_id: data.message_id, message: data.message});
    });
    socket.on('send-deleted-message', function(data) {
        io.emit('deleted-message'+data.id, data.message_id);
    });
    socket.on('new-user', function(id) {
        users[socket.id] = id;
        if (!user_ids.includes(id)){
            user_ids.push(id);
        }
        io.emit('user-connected', user_ids);
    });
    socket.on('disconnect', () => {
        user_ids.splice(user_ids.indexOf(users[socket.id]), 1);
        delete users[socket.id];
        io.emit('user-disconnected', user_ids);
    });
});