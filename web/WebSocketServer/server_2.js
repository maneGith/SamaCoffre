const WebSocket = require("ws").Server;
const HttpsServer = require('https').createServer;
//HttpsServer.listen(6001);

const fs = require("fs");

server = HttpsServer({
    cert: fs.readFileSync('/etc/letsencrypt/live/samacoffre.sn/fullchain.pem'),
    key: fs.readFileSync('/etc/letsencrypt/live/samacoffre.sn/privkey.pem')
});

wss = new WebSocket({
    server: server
});

const clients = []
wss.on('connection', function(ws){
    clients.push(ws)
    ws.on('message', function(message){
        var enc = new TextDecoder("utf-8");
        var msg = enc.decode(message);
        
        
        clients.forEach(client => client.send(msg))
        //this.send(enc.decode(message))
        
    })
    
});

server.listen(6001);
