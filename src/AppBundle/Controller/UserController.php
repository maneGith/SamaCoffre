<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\ValidateEmailAdress\VerifyEmail;
USE Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * User controller.
 *
 * @Route("user")
 */
class UserController extends Controller
{
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }
    /**
     * Creates a new user entity.
     *
     * @Route("/new", name="user_new")
     * @Method({"POST"})
     */
    public function newAction(Request $request)
    {
        //On evite l'execution de cette fonction pour user connecte
        if($this->get('security.authorization_checker')->isGranted('ROLE_USER')){
           if(!$this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE') && !$this->get('security.authorization_checker')->isGranted('ROLE_GUICHET')){
                return $this->redirectToRoute('homepage');
           }
        }
        
        
        $civilitecateg = $request->get('categ');
        $civilite = $request->get('civilite');
        $prenom = $request->get('prenom');
        $nom = $request->get('nom');
        $email = $request->get('email');
        //$password = $request->get('password');
        $telephone = $request->get('telephone');
        $action = $request->get('action');
        
        $visiteur = $request->get('visiteur');
        $password_request = $request->get('password');
        

        
        
        
//        $form = $this->createForm('AppBundle\Form\UserType', $user);
//        $form->handleRequest($request);
        
        $message='Precreation';
        //On texte l'existence d'un email
        
        // Initialize library class
        $mail = new VerifyEmail();
        // Set the timeout value on stream
        $mail->setStreamTimeoutWait(20);
        // Set debug output mode
        $mail->Debug= TRUE; 
        $mail->Debugoutput= 'html'; 
        // Set email address for SMTP request
        $mail->setEmailFrom('from@email.com');
//$email = 'idrissamane@ems.sn'; 
        // Check if email is valid and exist
        if(!$mail->check($email)){ 
            $message='EmailNoExist';
          
            return new JsonResponse([
               'message'=>$message,
               'email'=>$email,
//               'password'=>$password,
            ]);
        }

        if ($action=='@cruser$ab') {
            $user = new User();
            $em = $this->getDoctrine()->getManager();
            //Activation du utilisateur
            $user->setEnabled(true);
            
            //Attribution de mot de passe
            $password= $user->genererPassword();
            if($visiteur=='anonyme'){
               $password=$password_request;
            }
            $user->setPassword($password);
            
            
            //Atribution ROLE_COFFRE
            $roles[]='ROLE_COFFRE';
            $user->setRoles($roles);
            
            
            //Attribution civilité
            if($civilitecateg=='Particulier'){
                 $user->setCivilite($civilite);
                // $user->setPrenom(trim($prenom));
            }else{
                 $user->setCivilite($civilitecateg);
                 $civilite=$civilitecateg;
            }
            
            $user->setPrenom($prenom);
            $user->setNom($nom);
            $user->setEmail($email);
            $user->setUsername($email);
            $user->setTelephone($telephone);
            
            //Persist en BD
            try {
                 //var_dump('$email');        die();
                
                
                
                
                
            $message='Creation';
            $em->persist($user);
            $em->flush(); 
            
            
       
            
            
            
            
            
            
//            //Envoi du mail de Paramètres d'accès
//            //Creer le transport
//            $transport =(new \Swift_SmtpTransport('smtp.kheweul.org' , 25))
//                          ->setUsername('idrissamane@ems.sn')
//                          ->setPassword('doklosB12');
//            //Creer le transporteur
//            $mailer = new \Swift_Mailer($transport);
//            // Create a message
//            $messagemailer= (new \Swift_Message("senBPOST - Vos Paramètres d'accès"))
//                        ->setFrom('idrissamane@ems.sn')
//                        ->setTo($email)
//                        ->setBody(
//            $this->renderView(
//            // templates/emails/registration.html.twig
//                    'emails/CodeAccesUsers.html.twig',
//                    ['civilite' => $civilite, 
//                    'nom' => $nom,
//                    'email' => $email,
//                    'password' => $password, ]
//                ),
//                'text/html'
//            );
//              // Send the message
//            $result = $mailer->send($messagemailer);
                
 
            } catch (\Doctrine\DBAL\DBALException $ex) {
                $email=$ex->getPrevious()->getMessage();
                $message='Duplication';
            }
            
            
            return new JsonResponse([
               'message'=>$message,
               'civilite'=>$civilite,  
               'nom'=>$nom,
               'email'=>$email,
               'password'=>$password,
            ]);

        } else {
             return $this->redirectToRoute('login');
        }
        
//        return $this->render('user/new.html.twig', array(
//           // 'user' => $user,
//            //'form' => $form->createView(),
//        ));
    }
    
    
    /**
     *
     * @Route("/chercher", name="user_chercher")
     * @Method({"POST"})
     */
    public function chercherUserAction(Request $request)
    {
          //On evite l'execution de cette fonction pour user connecte
         if($this->get('security.authorization_checker')->isGranted('ROLE_USER')){
           return $this->redirectToRoute('homepage');
        }
        
        $em = $this->getDoctrine()->getManager();
        $chercher=new \AppBundle\Entity\Chercher();
        
        $form = $this->createForm('AppBundle\Form\ChercherType', $chercher);
        $form->handleRequest($request);
        $message='';
        $action = $request->get('action');
        $email = $request->get('email');
        
        if ($action=='@cruser$ab') {
 
                //var_dump($email);        die();
                $user = $em->getRepository('AppBundle:User')->findOneByEmail($email);
                if($user){
                    
                    /**
                     * Logic Envoi mail
                     */
                    
                    
                    $civilite=$user[0]->getCivilite();
                    $nom=$user[0]->getNom();
                    $password=$user[0]->getPassword();
                    
                    //Envoi du mail de Paramètres d'accès
                    //Creer le transport
                    $transport =(new \Swift_SmtpTransport('pro2.mail.ovh.net' , 587, 'tls'))
                                  ->setUsername('samacoffre@samacoffre.sn')
                                  ->setPassword('doklosB12');
                    //Creer le transporteur
                    $mailer = new \Swift_Mailer($transport);
                    // Create a message
                    $messagemailer= (new \Swift_Message("samaCOFFRE - Rappel Mot de Passe"))
                                ->setFrom('samacoffre@samacoffre.sn')
                                ->setTo($email)
                                ->setBody(
                    $this->renderView(
                    // templates/emails/registration.html.twig
                            'emails/RappelPWD.html.twig',
                            ['civilite' => $civilite, 
                            'nom' => $nom,
                            'email' => $email,
                            'password' => $password, ]
                        ),
                        'text/html'
                    );
                    // Send the message
                    $result = $mailer->send($messagemailer);
                    
                    $message='oui';
                } else {
                    
                    $message='non'; 
                }
                //Renvoie form et message de notification

                return new JsonResponse([
                       'message'=>$message,
                ]);
                
        }else {
             return $this->redirectToRoute('login');
        }
       
       
//        return $this->render('user/chercher.html.twig', array(
//                'form' => $form->createView(),
//        ));
    }
    
    
    /**
     *
     * @Route("/sendMailUserCreated", name="user_sendMailUserCreated")
     * @Method({"POST"})
     */
    public function sendMailUserCreatedAction(Request $request)
    {
          //On evite l'execution de cette fonct
        
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') && !$this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE') && !$this->get('security.authorization_checker')->isGranted('ROLE_GUICHET')){
           return $this->redirectToRoute('homepage');
        }
        
        $message='non';
        $action = $request->get('action');
        $civilite = $request->get('civilite');
        $nom = $request->get('nom');
        $password = $request->get('password');
        $email = $request->get('email');
        
        $user=$this->getUser();
        $em = $this->getDoctrine()->getManager();
        $entreprise='';
        
        if($this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE') ){
               $userEntreprise=$em->getRepository('AppBundle:AdminEntreprise')->findOneByUser($user)[0];
               $EntrepriseuUser=$userEntreprise->getEntreprise();
               $entreprise=$EntrepriseuUser->getNom();
        } else {
            $userEntreprise=$em->getRepository('AppBundle:Guichet')->findOneByUser($user)[0];
               $EntrepriseuUser=$userEntreprise->getAgence()->getEntreprise();
               $entreprise=$EntrepriseuUser->getNom();
        }
       
        
        if ($action=='@cruser$ab') {

            //Envoi du mail de Paramètres d'accès
                  //Creer le transport
                  $transport =(new \Swift_SmtpTransport('pro2.mail.ovh.net' , 587, 'tls'))
                                ->setUsername('samacoffre@samacoffre.sn')
                                ->setPassword('doklosB12');
                  //Creer le transporteur
                  $mailer = new \Swift_Mailer($transport);
                  // Create a message
                  $messagemailer= (new \Swift_Message("samaCOFFRE - Paramètres d'Accès Coffre"))
                              ->setFrom('samacoffre@samacoffre.sn')
                              ->setTo($email)
                              ->setBody(
                  $this->renderView(
                  // templates/emails/registration.html.twig
                          'emails/CodeAccesUsers.html.twig',
                          ['civilite' => $civilite, 
                          'nom' => $nom,
                          'email' => $email,
                          'password' => $password,
                          'entreprise' => $entreprise,]
                      ),
                      'text/html'
                  );
                  // Send the message
                  $mailer->send($messagemailer);
                  $message='oui';
        }
        //CallBack
        return new JsonResponse([
                       'message'=>$message,
        ]);

    }
    
    /**
     * @Route("/password", name="password")
     */
    public function passwordAction(Request $request)
    {
        $user=$this->getUser();
        
        // replace this example code with whatever you need
        $currentPWD =  $request->get('currentPWD');
        $firstPWD   =  $request->get('firstPWD');
        $secondPWD  =  $request->get('secondPWD');
        
        $action = $request->get('action');
        $message=0;
        
        if ($action=='@cruser$ab') {
            $em = $this->getDoctrine()->getManager();
            //Verification PWD
            $encoderService = $this->container->get('security.password_encoder');
            $user=$this->getUser();
            $match = $encoderService->isPasswordValid($user, $currentPWD);
            if(!$match){
                $message=1; 
            }else{
                if($firstPWD == $secondPWD && $firstPWD!=null){
                    
                    $message=2; 
                    //var_dump($message);die();
                    $firstPWD=$encoderService->encodePassword($user, $firstPWD);
                    $user->setPassword($firstPWD);

                    $em->persist($user);
                    $em->flush(); 
                }
             }

             //var_dump($match);die();
            
            return new JsonResponse([
                'message'=>$message,
            ]);
        }

        

         
        


        return $this->render('security/password.html.twig', [
//            'error' => $error,
            'user' => $user,
        ]);
    }
    
    
        
    /**
     * @Route("/password/find", name="password_find")
     */
    public function passwordFindAction(Request $request)
    {
        $action = $request->get('action');
        $currentPWD =  $request->get('currentPWD');
        $message='non';
        //Controle Action Normal Request
        if($action!='@cruser$ab') {
          return $this->redirectToRoute('password');   
        }
        
        $encoderService = $this->container->get('security.password_encoder');
        $user=$this->getUser();
        $match = $encoderService->isPasswordValid($user, $currentPWD);
        if($match){
            $message='oui'; 
        }
            
        return new JsonResponse([
                'message'=>$message,
        ]); 
    }
   
    
    /**
     * @Route("/email/find", name="email_find")
     */
    public function emailFindAction(Request $request)
    {
        //On evite l'execution de cette fonction pour user connecte
        if($this->get('security.authorization_checker')->isGranted('ROLE_USER')){
           return $this->redirectToRoute('homepage');
        }
        
        $action = $request->get('action');
        $email =  $request->get('email');
        $message='non';
        //Controle Action Normal Request
        if($action!='@cruser$ab') {
          return $this->redirectToRoute('login');   
        }
        
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->findOneByEmail($email);
         
       
        if($user){
            $message='oui'; 
        }
            
        return new JsonResponse([
                'message'=>$message,
        ]); 
    }
    
    /**
     * @Route("/email/find/state", name="email_find_state")
     */
    public function emailFindStateAction(Request $request)
    {
        $action = $request->get('action');
        $email =  $request->get('email');
        $message=0;
        //Controle Action Normal Request
        if($action!='@cruser$ab') {
          return $this->redirectToRoute('login');   
        }
        
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->findOneByEmail($email);
         
       //Email utilise
        if($user){
            $message=1;
            return new JsonResponse([
                'message'=>$message,
            ]); 
        }else {
            
            // Initialize library class
            $mail = new VerifyEmail();
            // Set the timeout value on stream
            $mail->setStreamTimeoutWait(20);
            // Set debug output mode
            $mail->Debug= TRUE; 
            $mail->Debugoutput= 'html'; 
            // Set email address for SMTP request
            $mail->setEmailFrom('from@email.com');
            // Check if email is valid and exist
            if(!$mail->check($email)){ 
                $message=2;
                return new JsonResponse([
                   'message'=>$message,
                ]);
            }
        }
        
        //Email introuvable
        
        
        
            
        return new JsonResponse([
                'message'=>$message,
        ]); 
    }
    
    
    
    /**
     * @Route("/email/send", name="email_send")
     */
    public function emailSendAction(Request $request)
    {
       $message='dds';
       $email='idrissamane@ems.sn';
       
       //Envoi du mail d'identifants de connexion
                //Creer le transport
                   $transport =(new \Swift_SmtpTransport('pro2.mail.ovh.net' , 587, 'tls'))
                              ->setUsername('contact@samacoffre.sn')
                              ->setPassword('doklosB12');
                //Creer le transporteur
                $mailer = new \Swift_Mailer($transport);
                  
                  
                   // Create a message
                $messagemailer= (new \Swift_Message("senBPOST - Vos Paramètres d'accès"))
                            ->setFrom('contact@samacoffre.sn')
                            ->setTo($email)
                            ->setBody(
                $this->renderView(
                // templates/emails/registration.html.twig
                        'emails/CodeAccesUsers.html.twig',
                        ['civilite' => 'Mr', 
                        'nom' => 'Mane',
                        'email' => 'Admin',
                        'password' => 'pass', ]
                    ),
                    'text/html'   
                );
                  
                  // Send the message
        $result = $mailer->send($messagemailer);
       
        return new JsonResponse([
                'message'=>$message,
        ]); 
    }
    
    
    
  /**
     * @Route("/email/valid", name="email_valid")
     */
    public function emailValidAction(Request $request)
    {
        $message='';
        // Initialize library class
        $mail = new VerifyEmail();
        
        // Set the timeout value on stream
$mail->setStreamTimeoutWait(20);

// Set debug output mode
$mail->Debug= TRUE; 
$mail->Debugoutput= 'html'; 

// Set email address for SMTP request
$mail->setEmailFrom('idrissamane@ems.sn');

// Email to check
//$email = 'idrissamane@ems.sn'; 
$email = 'senbpost@gmail.com'; 
//$email = 'mamadou.ba@laposte.sn'; 
//$email = 'senbpost@gmail.com'; 
// Check if email is valid and exist
if($mail->checkEmail($email)){ 
   $message = 'Email '.$email.' is exist!'; 
}else{ 
   $message =  'Email '.$email.' is not exist!'; 
} 
         
        return new JsonResponse([
                'message'=>$message,
        ]); 
    }   
    
    
    
    
    
    
    
    
    
    
    
    
    
    

    /**
     * Finds and displays a user entity.
     *
     * @Route("/show", name="user_show")
     * @Method("GET")
     */
    public function showAction()
    {   
        $user=$this->getUser();
       

        return $this->render('user/show.html.twig', array(
            'user' => $user,
           
        ));
    }

    /**
     * Displays a form to edit an existing user entity.
     *
     * @Route("/edit", name="user_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request)
    {    
        
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_COFFRE')){
           return $this->redirectToRoute('user_show');
        }
        
        $user=$this->getUser();
       
        $editForm = $this->createForm('AppBundle\Form\UserType', $user);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_edit', array('id' => $user->getId()));
        }

        return $this->render('user/edit.html.twig', array(
            'user' => $user,
            'edit_form' => $editForm->createView(),
            
        ));
    }
    
     /**
     * @Route("/reception", name="user_boite")
     */
    public function boiteAbonneAction(Request $request)
    {
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_COFFRE')){
           return $this->redirectToRoute('homepage');
        }
        
        $em = $this->getDoctrine()->getManager();
        $user=$this->getUser();
        
           //Infos Entreprise
            
        $informations=array();
        $infosResult=$em->getRepository('AppBundle:AccesReferenceService')->findByUserService($user, 'Documents Personnels');
        
        
        for ($i=0; $i<count($infosResult); $i++){
            $entreprise = $infosResult[$i];
            $EntrepriseInfos['entreprise']=$entreprise;

            $infos= $em->getRepository('AppBundle:InfoEntreprise')->findInformationsByEntreprise($entreprise["id"]);
            $EntrepriseInfos['infos']=$infos;
            //var_dump($bord);die();
            $informations[$i]=$EntrepriseInfos;
        }

        //die();
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'accueil' => '',
            'user' =>$user,
            'informations' =>$informations,
        ]);
//        
//        $courriers=$em->getRepository('AppBundle:CourrierEntreprise')->findByUsager($user);
//         //var_dump(count($courriers));die();
//      
//        // replace this example code with whatever you need
//        return $this->render('user/courriers.html.twig', array(
//            'courriers' => $courriers,
//            'user' => $user,
//        ));
        
        
    }
    
    /**
     * @Route("/nletterrnb", name="user_nletterrnb")
     */
    public function nbLettreAction()
    {
        //blocage
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_COFFRE')){
           return $this->redirectToRoute('homepage');
        }
        
        $html='';
        $user=$this->getUser();
        $em = $this->getDoctrine()->getManager();
        
        $nbletters=$em->getRepository('AppBundle:CourrierEntreprise')->findByUsagerRead($user);
        $luescourr=$em->getRepository('AppBundle:CourrierEntreprise')->findByUsager($user);
        
        if($nbletters){
            $html .= '<span id="spannote" style="float:right"><a href="'.$this->router->generate('user_boite').'"> <i class="fa fa-envelope fa-2x"  style="color: #265ead;margin-left: -80%;margin-top:-7px"></i></a>'; 
            $html .= '<span id="id'.$user->getId().'"'.' class="badge badge-danger note-number" style="border-radius: 50%;position: absolute;top: -5px">';
            
            $html .= count($nbletters);
  
            $html .='</span></span>';
            
            //var_dump(count($userGuichetEntreprise));            die();
        }
        elseif ($luescourr) {
            $html .= '<span id="spannote" style="float:right;"><a href="'.$this->router->generate('user_boite').'"> <i class="fa fa-envelope fa-2x" style="color: #265ead;margin-left: -80%;margin-top:-7px"></i></a>'; 
        
            $html .= '<span id="id'.$user->getId().'"'.' class="badge badge-danger note-number" style="border-radius: 50%;position: absolute;top: -5px">';
            $html .='</span></span>';
        } 
        else {
            $html .= '<span id="spannote" style="float:right;visibility:hidden"><a href="'.$this->router->generate('user_boite').'"> <i  class="fa fa-envelope fa-2x"  style="color: #265ead;margin-left: -80%;margin-top:-7px"></i></a>'; 
            $html .= '<span id="id'.$user->getId().'"'.' class="badge badge-danger note-number" style="border-radius: 50%;position: absolute;top: -5px">';
            $html .='</span></span>';
        }

        return new Response($html);
    }
    
    
    
    /**
     * @Route("/rappel", name="user_pwdChange")
     */
    public function pwdChangeAction()
    {
        //blocage
        if($this->get('security.authorization_checker')->isGranted('ROLE_USER')){
           return $this->redirectToRoute('homepage');
        }
        
        return $this->render('user/chercher.html.twig');
    }


}
