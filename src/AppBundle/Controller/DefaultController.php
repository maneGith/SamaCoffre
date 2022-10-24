<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use AppBundle\Entity\AdminEntreprise;
use AppBundle\Entity\EntrepriseService;

class DefaultController extends Controller
{
    /**
     * @Route("/home", name="homepage")
     */
    public function indexAction(Request $request)
    {   
        $user=$this->getUser();
        $em = $this->getDoctrine()->getManager();
        
        
       
          //Coffre
        if($this->get('security.authorization_checker')->isGranted('ROLE_COFFRE')){
              
             $courriers=$em->getRepository('AppBundle:CourrierEntreprise')->findByUsager($user);
              //var_dump(count($courriers));die();

             // replace this example code with whatever you need
             return $this->render('user/courriers.html.twig', array(
                 'courriers' => $courriers,
                 'user' => $user,
             ));
        } else {
            
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
        
        }
       
        
        
    }
    
    /**
     * Lists all entrepriseService entities.
     *
     * @Route("/entrepriseservice/entreprise", name="entrepriseservice_entreprise")
     */
    public function entrepriseAction(Request $request)
    {  
        $entreprise='';
        $form='';
        $user=$this->getUser();
        $em = $this->getDoctrine()->getManager();
        $serviceDisponibles='';
        
       
         
         //Poids Unitaire
        $poidsTarif=120;
        $nbrTarif=4;
        $poidsunitaire=$poidsTarif*$nbrTarif;
        
        $guichentreprise='';
        
        if($this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
            //findOneByUser($user)
            $admentreprise = $em->getRepository('AppBundle:AdminEntreprise')->findOneByUser($user)[0];
            $entreprise=$admentreprise->getEntreprise();
            
            //Creating ou Souscription
            $entrepriseService = new Entrepriseservice();
            $entrepriseService->setEntreprise($entreprise);
            $form = $this->createForm('AppBundle\Form\EntrepriseServiceType', $entrepriseService);
            $form->handleRequest($request);
            if($admentreprise->getEntreprise()->getCategorie()=='Banque'){
                $serviceDisponibles=$em->getRepository('AppBundle:Tarif')->findByCategorieBanque(); 
//            }elseif ($admentreprise->getEntreprise()->getCategorie()=='Facturier') {
//                $serviceDisponibles=$em->getRepository('AppBundle:Tarif')->findByCategorieFacturier(); 
            } else {
                 $serviceDisponibles=$em->getRepository('AppBundle:Tarif')->findByCategorieEntreprise();  
            }
           
            $service= $request->get('service');
            
            if ($form->isSubmitted() && $form->isValid()) {
                  //var_dump('ff');            die();
                $service=$em->getRepository('AppBundle:Tarif')->findOneById($service);  
                if(!$service){
                      return $this->redirectToRoute('entrepriseservice_entreprise');
                }
                $service=$service[0];
                 
                $isEtrServiceDeja=$em->getRepository('AppBundle:EntrepriseService')->findByEntrepriseService($entreprise, $service);
                if($isEtrServiceDeja){
                    return $this->redirectToRoute('entrepriseservice_entreprise');
                } 
                
               
                $entrepriseService->setService($service);
                $entrepriseService->setDroitinout('Client');
                $entrepriseService->setDroitguichet('Lister');
               
                $em->persist($entrepriseService);
                $em->flush();

                return $this->redirectToRoute('entrepriseservice_entreprise');
            }
            $form=$form->createView();
        
            //var_dump($entreprise->getNom());            die();
        }elseif ($this->get('security.authorization_checker')->isGranted('ROLE_GUICHET')) {
            $guichentreprise = $em->getRepository('AppBundle:Guichet')->findOneByUser($user)[0];
            $entreprise=$guichentreprise->getAgence()->getEntreprise();
            
        } else {
              //var_dump('ROLE_GUICHET');            die();
            return $this->redirectToRoute('homepage');
        }
        
        $entrepriseServices = $em->getRepository('AppBundle:EntrepriseService')->findByEntreprise($entreprise);
        
          //var_dump(count($entrepriseServices));            die();
         //Vue Listing
        
        return $this->render('default/serviceEntreprise.html.twig', array(
            'entrepriseServices' => $entrepriseServices,
            'entreprise' => $entreprise,
            'user' => $user,
            'form' => $form,
            'poidsunitaire' => $poidsunitaire,
            'serviceDisponibles' => $serviceDisponibles,
            'guichentreprise' => $guichentreprise
        ));
        
    }
    
    
    /**
     * Lists all entrepriseService entities.
     *
     * @Route("/condition/utilisation", name="condition_utilisation")
     */
    public function conditionAction(Request $request)
    {  
        if($this->get('security.authorization_checker')->isGranted('ROLE_USER')){
           return $this->redirectToRoute('homepage');
        }
        $client=$request->get('client');
       
         //Vue Conditions Generals d'Utilisation
        return $this->render('default/conditions.html.twig', array('conditions'=>'','client'=>$client));
        
    }
    
    /**
     * Lists all tarif clients.
     *
     * @Route("/tarif/client", name="tarif_client")
     */
    public function tarifAction(Request $request)
    {  
        if($this->get('security.authorization_checker')->isGranted('ROLE_USER')){
           return $this->redirectToRoute('homepage');
        }
       
         //Vue Conditions Generals d'Utilisation
        return $this->render('default/tarifs.html.twig', array('conditions'=>''));
        
    }
    
    /**
     * Lists all tarif clients.
     *
     * @Route("/tarif/client/show", name="tarif_client_show")
     */
    public function tarifVueClientAction(Request $request)
    {  
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
           return $this->redirectToRoute('homepage');
        }
       $user=$this->getUser();
         //Vue Conditions Generals d'Utilisation
        return $this->render('default/VuTarifClient.html.twig', array('user'=>$user));
        
    }
    
     /**
     * Lists all tarif clients.
     *
     * @Route("/guide/client", name="guide_client")
     */
    public function GuideClientAction(Request $request)
    {  
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
           return $this->redirectToRoute('homepage');
        }
       
         $user=$this->getUser();
         //Vue Conditions Generals d'Utilisation
        return $this->render('default/GuideClient.html.twig', array('user'=>$user, 'accueil' => ''));
        
    }
    
    /**
     * Lists all entrepriseService entities.
     *
     * @Route("/info/entreprise", name="info_client")
     */
    public function infoClientAction(Request $request)
    {  
        if($this->get('security.authorization_checker')->isGranted('ROLE_USER')){
           return $this->redirectToRoute('homepage');
        }
        
         $client=$request->get('client');
        //Ceation
        $adminEntreprise = new Adminentreprise();
        $action = $request->get('action');
        if ($action=='@cruser$ab') {
            $em = $this->getDoctrine()->getManager();
            
            $userForm= new User();
            
            $adresse = $request->get('adresse');
            $adminEntreprise->getEntreprise()->setAdresse($adresse);
            //Infos User From Form
            $email = $request->get('email');
            $userForm->setEmail($email);
            $userForm->setUsername($email);
            
            $telephone = $request->get('telephone');
            $userForm->setTelephone($telephone);

            $password=$userForm->genererPassword();
            $userForm->setPassword($password);
             
            $roles[]='ROLE_ENTREPRISE';
            $userForm->setRoles($roles);
             
            $userForm->setEnabled(true);
            
             $userForm->setNom($adminEntreprise->getEntreprise()->getNom());
             //$adminEntreprise->getEntreprise()->getNom($entreprise);
             
            $adminEntreprise->setUser($userForm);
            try {
                    $em->persist($adminEntreprise);
                    $em->flush();
                    //Envoi de Mail Identification
                    $nom=$adminEntreprise->getEntreprise()->getNom();
                    $transport =(new \Swift_SmtpTransport('smtp.kheweul.org' , 25))
                                ->setUsername('idrissamane@ems.sn')
                                ->setPassword('doklosB12');
                    //Creer le transporteur
                    $mailer = new \Swift_Mailer($transport);
                    // Create a message
                    $messagemailer= (new \Swift_Message("samaCOFFRE - Accès Aministration"))
                                ->setFrom('idrissamane@ems.sn')
                                ->setTo($email)
                                ->setBody(
                    $this->renderView(
                    // templates/emails/registration.html.twig
                            'emails/CodeAccesEntreprise.html.twig',
                            ['nom' => $nom,
                            'password' => $password, ]
                        ),
                        'text/html'
                    );
                    // Send the message
                    $mailer->send($messagemailer);
                    
                    return $this->redirectToRoute('adminentreprise_show', array('id' => $adminEntreprise->getId()));
            } catch (\Doctrine\DBAL\DBALException $ex) {
                    return $this->redirectToRoute('info_client');
            }
            
        }//Fin
        
         //Vue Conditions Generals d'Utilisation
        return $this->render('default/InfoInscriptionCient.html.twig', array('conditions'=>'','client'=>$client));
        
    }
    
    
     /**
     * @Route("/", name="login")
     */
    public function loginAction(Request $request, AuthenticationUtils $authenticationUtils)
    {
        if($this->get('security.authorization_checker')->isGranted('ROLE_USER')){
           return $this->redirectToRoute('homepage');
        }
        $error=$authenticationUtils->getLastAuthenticationError();
        $LastUserName=$authenticationUtils->getLastUsername();
       
        //var_dump('$abonnes');die();
         $em = $this->getDoctrine()->getManager();
         $users=$em->getRepository('AppBundle:User')->findAll();
         $entreprises=$em->getRepository('AppBundle:Entreprise')->findAll();
         $nbusers= count($users);
         $nentreprises= count($entreprises);
         
        return $this->render('security/login.html.twig', array(
            'username'=>$LastUserName,
            'error'=>$error,
              'nbusers'=>$nbusers,
            'nentreprises'=>$nentreprises,
        ));
    }
    
    
    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction()
    {

    }
}
