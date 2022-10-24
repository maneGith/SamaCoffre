var url = 'wss://samacoffre.sn:6001/websocket'
const server = new WebSocket(url)

const button = document.getElementById('send')
button.disabled = true
button.addEventListener('click', sendMessage, false)

server.onopen = function(){
    button.disabled = false     
    var jsonmsg = {
    courrIDJson: "555",
    clientIDJson:   "6",
    actionJson: "envoyer"
  };
  
    server.send(JSON.stringify(jsonmsg))
}




function sendMessage(){
    alert(1);
    var json = {
        courrIDJson: "777",
        clientIDJson:   "6",
        actionJson: "envoyer"
    };
    
    server.send(JSON.stringify(json))
}