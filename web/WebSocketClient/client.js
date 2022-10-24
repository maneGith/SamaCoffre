var url = 'wss://samacoffre.sn:6001/websocket'
const server = new WebSocket(url)

const button = document.getElementById('send')
const noteClassID = document.getElementsByClassName("note-number")[0].getAttribute("id");
const chatbox     = document.getElementsByClassName("chatbox")

//button.disabled = true
//button.addEventListener('click', sendMessage, false)

server.onopen = function(){
//        button.disabled = false   
//        
//    var jsonmsg = {
//    courrIDJson: "555",
//    clientIDJson:   "6",
//    actionJson: "envoyer"
//  };
//  
//    server.send(JSON.stringify(jsonmsg))
}

server.onmessage = function(event){
   var msg ;
   
   if (event.data instanceof Blob) {
       
        reader = new FileReader();
        reader.onload = () => {
            msg = JSON.parse(reader.result);
            
//            console.log(msg);
            
            var noteID = 'id' + msg.clientIDJson
            var actionJS =  msg.actionJson

            if(noteID==noteClassID){

                const notSpan=document.getElementById(noteID)
                var noteNumber = notSpan.innerText;
                if(actionJS=='envoyer'){
                    //Debut Ajout Courrier
                    if(noteNumber=='')
                       noteNumber=1
                    else
                       noteNumber=parseInt(noteNumber)+1

                    notSpan.innerText = noteNumber;
                    document.getElementById("spannote").style.visibility = "visible";

                    if(chatbox.length > 0){
                        var table = chatbox[0];
                        var row = table.insertRow(0);
                        row.setAttribute("id", "cr"+msg.courrIDJson);

                        var cell1 = row.insertCell(0);
                        cell1.setAttribute("data-label", "Document");
                        cell1.setAttribute("class", "col");


                        var cell2 = row.insertCell(1);
                        cell2.setAttribute("data-label", "Envoyeur");

                        var cell3 = row.insertCell(2);
                        cell3.setAttribute("data-label", "Date");

                        var cell4 = row.insertCell(3);
                        cell4.setAttribute("class", "action");
                        cell4.setAttribute("style", "min-width: 250px");   

                        var service='';
                        if(msg.serviceJson!='Documents Personnels'){
                             cell1.innerHTML = '<span style="font-weight: bold">'+msg.serviceJson+'</span>';
                             service=msg.serviceJson;
                        }
                        else{
                             cell1.innerHTML = '<span style="font-weight: bold">'+msg.natureJson+'</span>';
                               service=msg.natureJson;
                        }
                        cell2.innerHTML = msg.entreprJson;
                        cell3.innerHTML = msg.dateenvJson;

                        var action = '<a href="'+msg.routeDocJson+'" class="btn btn-ems r-ems openDoc" style="width: 105px;">Ouvrir</a>&nbsp;';
                        action = action + '<span class="spantelechargement teleInfo"  rel="'+msg.courrIDJson+'"><a href="/uploads/documents/'+msg.pathPDFJson+'" class="btn btn-ems r-ems" download="'+service+'-'+msg.entreprJson+'-'+msg.dateenvJson+'" style="width: 105px;">Télécharger</a></span>';
                        cell4.innerHTML = action;

                        window.scrollTo({top: 0});
                    }//Fib Ajout Courrier
                }else{
                    if(noteNumber=='')
                        noteNumber='';
                    else{
                        if(noteNumber==1){
                            notSpan.innerText = '' ;
                        }else{
                            noteNumber=parseInt(noteNumber)-1;
                            notSpan.innerText = noteNumber ;
                        }
                    }
                    if(chatbox.length > 0){

                        var rowcr = document.getElementById('cr'+msg.courrIDJson);
                        rowcr.parentNode.removeChild(rowcr); 
                    }

                }
           }
   
   //generateNotificationNumber(event.data)

        };
        reader.readAsText(event.data);
        
    } else {
        console.log("Result: " + event.data);
    }
    
    //console.log(event.data);
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