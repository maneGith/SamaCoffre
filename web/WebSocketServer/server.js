const WebSocket = require("ws").Server;
const HttpsServer = require('https').createServer;
const fs = require("fs");

const server = HttpsServer({
    cert: fs.readFileSync("/etc/letsencrypt/live/samacoffre.sn/fullchain.pem"),
    key: fs.readFileSync("/etc/letsencrypt/live/samacoffre.sn/privkey.pem")
});

const wss = new WebSocket({
    server: server
});

const clients = []
wss.on('connection', function(ws){
    clients.push(ws)
    ws.on('message', function(data){
        clients.forEach(client => client.send(data))
    })
});
server.listen(6001);

console.log('Server connecté');

