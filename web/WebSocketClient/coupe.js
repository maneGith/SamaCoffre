{% if(is_granted('ROLE_ADMIN') or is_granted('ROLE_ENTREPRISE') or is_granted('ROLE_GUICHET')) %}  
                           
    //Real Time Logic
            //var url = 'ws://www.samacoffre.sn:6001/websocket'
            var url = 'wss://samacoffre.sn:6001/websocket'
            var server = new WebSocket(url)
            
            const button = document.getElementById('send')
            
            button.disabled = true
            button.addEventListener('click', sendMessage, false)
            
            function sendMessage(){
                alert(1);
                var json = {
                    courrIDJson: "777",
                    clientIDJson:   "6",
                    actionJson: "envoyer"
                };

                server.send(JSON.stringify(json))
            }
           

            server.onopen = function(){
        button.disabled = false   
                var jsonmsg = {
                    courrIDJson: "333",
                    clientIDJson: 6,
                    actionJson: "envoyer"
                };
                //server.send(JSON.stringify(jsonmsg));
                
                 var jsonsec = {
                    courrIDJson: "444",
                    clientIDJson: 6,
                    actionJson: "envoyer"
                };
                
                
                 var jsonsec1 = {
                    courrIDJson: "666",
                    clientIDJson: 6,
                    actionJson: "envoyer"
                };
                
                
                 var jsonsec2 = {
                    courrIDJson: "777",
                    clientIDJson: 6,
                    actionJson: "envoyer"
                };
                
                
                   var json = {
                    courrIDJson: [jsonmsg, jsonsec, jsonsec1, jsonsec2],
                    clientIDJson: 6,
                    actionJson: "envoyer"
                };
                
                 server.send(JSON.stringify(json));
                 
            }

            {#var TIMEOUT = 10;
            var lastTime = (new Date()).getTime();

            setInterval(function() {
              var currentTime = (new Date()).getTime();

              if (currentTime > (lastTime + TIMEOUT + 60)) {
                // Wake!
                 if (!isOpen(server)) {
                       // console.log('computer woke up!');
                        //window.location.reload();
                 }

              }
              lastTime = currentTime;
            }, TIMEOUT);#}
            
            
            
          //console.log(isOpen(server));
            
            function isOpen(ws) { 
                return ws.readyState === ws.OPEN 
            }
            
            
        //Suppression courriers
        $('body').on("click",'.supcourriers', function(){
            var url =$(this).attr('url');
            $("#modalFormSupp").attr("action", url);
            $("#myModal" ).css({'display':'block'});
            
            var categ = $( this ).hasClass( "categ" );
            //alert(categ);
            $("#modalInfoSupp").html(''); 
               var periode ='';
            if(!categ){
                periode = $(this).parent().parent().parent().children().first().html();
            }else{
                var valCateg =$(this).attr('rel');
                if(valCateg==1){
                   periode='Référencés'; 
                }
                else if(valCateg==2){
                 periode='Non Référencés';     
                }
                else{
                   periode='Ensemble';   
                }
               
            }
            $("#modalInfoSupp").html(periode);  
            
            
        });
        
        $( "#modalFormSupp" ).submit(function( event ) {
            event.preventDefault(); // avoid to execute the actual submit of the form.  
            var form = $(this);
            var url =  form.attr('action')
            
            $.ajax({
                url:url,
                success: function(response){
                    //alert(response.clientIDJson);
                       //server.send(JSON.stringify(response))
                     //location.reload(); 
                    server.onopen = function(event){
                        response.forEach(element => server.send(JSON.stringify(element)));
                    }
                   
                    location.reload(); 
                        //console.log(response);
                },
                error: function (request, status, error) {
                    alert('errorss');
                }
            });
        });
        
        //Autorisation Acces Attribution et Suppression
        $( ".form_acces" ).submit(function( event ) {
            event.preventDefault(); // avoid to execute the actual submit of the form. 

            //Recuperation Input Value
            var reference = $.trim($(this).find('.reference').val());
            var email = $(this).find('input[name="email"]').val();
            var service = $(this).find('input[name="service"]').val();
            
            var form = $(this);
            var urlaction =  form.attr('action')
            //alert(urlaction);


             //alert(service);

            //Affichage erreurs
            if(reference=='') {  
                $('#errorRefNull').show();                    
            } 
            if(!validateEmail(email)) {  
                $('#errorinvalid').show();                    
            }

            var url =  '{{ path('accces_controle') }}';
            var action ='@cruser$ab'; 
            $.ajax({
                    url:url,
                    data:{
                        "service":service,
                        "reference":reference,
                        "email":email,
                        "action":action
                    },
                    success: function(response){
                        if(response.message==1){
                            $('#errorfound').show();
                        }else if(response.message==2){
                                $('#errorfound').show();
                        }else if(response.message==3){
                                $('#errorRefAttr').show();
                        }
                        else{
                              //Send Autorisation
                                $.ajax({
                                    url:urlaction,
                                    data:{
                                        "email":email,
                                        "reference":reference,
                                        "action":action
                                    },
                                    success: function(response){
                                       //alert(response.message);
                                        server.onopen = function(event){
                                            response.forEach(element => server.send(JSON.stringify(element)));
                                        }
                                       
                                        location.reload(); 
                                    },
                                    error: function (request, status, error) {
                                        alert(request.responseText);
                                         console.log(request.responseText)
                                    }
                                });
                        }                    

                    },
                    error: function (request, status, error) {
                        alert(request.responseText);
                    }
            });
        });
        
        //Suppression  Autorisation
        $('body').on("click",'#deleteAuto', function(){
            var url =$(this).attr('url');
            var action ='@cruser$ab'; 
            var serv = $(this).attr('rel');
            //alert(serv)
            $.ajax({
                url:url,
                data:{
                    "action":action
                },
                success: function(response){
                   //alert(response.message);
                    server.onopen = function(event){
                        response.forEach(element => server.send(JSON.stringify(element)));
                    }
                    
                   //location.reload(); 
                    var path = '{{path('accesreferenceservice_index')}}';
                    path= path+'?id='+serv;
                    
{#                  location.href = {{path('accesreferenceservice_index', { 'id': 1 })}};#}
        
                    window.location.href=path;
                },
                error: function (request, status, error) {
                    alert(request.responseText);
                }
            });
        });
        
       
        
    
    //Add in DropZone
    $("input[name='naturedoc']").click(function(){
     $('#TypeCourrierPersonnel').removeClass( "border border-danger" );
    });
    
    Dropzone.options.dropzoneFrom = {
        url: '{{path('courrierentreprise_new')}}', 
        autoProcessQueue:false,
        acceptedFiles:".pdf",
        autoDiscover: false, 
        init: function() {  
            var submitButton=document.querySelector('#submit-all');
            var totalFiles = 0;
            var myDropzone=this;
            
            
           
            
            submitButton.addEventListener("click", function(){
                
                if (!$("input[name='naturedoc']:checked").val()) {
                    $('#TypeCourrierPersonnel').addClass( "border border-danger" );
                    return false;
                }
                myDropzone.processQueue();
            
            //alert(TIMEOUT);
                 
                
            });
            
            
            this.on("addedfile", function (file) {
                totalFiles += 1;
                document.getElementById('numberFilesUpload').innerHTML = totalFiles+' fichiers';
            });
            this.on("removedfile", function (file) {
                totalFiles -= 1;
                document.getElementById('numberFilesUpload').innerHTML = totalFiles+' fichiers';
            });
            this.on("success", function (file, response) {
                
                  server.onopen = function(event){
                     console.log(response)
                    server.send(response)
                }
               
             // alert(response.routeDocJson);
                //console.dir(response);
               
                myDropzone.processQueue();
            });
            this.on('error', function(file, response) {
                   alert(response);
            });
            this.on("complete",function(){
                if(this.getQueuedFiles().length==0 && this.getUploadingFiles().length==0){
                    var _this=this;
                    _this.removeAllFiles();
                   // location.reload(); 
                }
                
            });
        },
    };
    
    // Ouverture DropZone
    $("#opendropzonePDF").click(function(){    
        $("#dropzonePDF").show();
        //alert($(this).text());
        var textbutton =  $(this).text();
        if(textbutton=='Déposer'){
             $(this).text("Clore");
        }else{
            $(this).text("Déposer");
            $("#dropzonePDF").hide();
        }
       
        
    });
    //Vider DropZone
    $("#cleardropzonePDF").click(function(){    
      Dropzone.forElement('#dropzoneFrom').removeAllFiles(true);
    });
    
{% endif %}