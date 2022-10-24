const WebSocket = require('ws')

const wss = new WebSocket.Server({
    port: 6001
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
