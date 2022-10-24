<?php

namespace AppBundle\Controller;

use AppBundle\Entity\CourrierEntreprise;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
//use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * Courrierentreprise controller.
 *
 * @Route("courrierentreprise")
 */
class CourrierEntrepriseController extends Controller
{
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }
    /**
     * Lists all courrierEntreprise entities.
     * Acces Nombre Docs
     * @Route("/", name="courrierentreprise_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        //Contole profil
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') && !$this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
           return $this->redirectToRoute('homepage');
        }
      
        $user=$this->getUser();
        $em = $this->getDoctrine()->getManager();
        $id =  $request->get('id');
        $EntrepriseService = $em->getRepository('AppBundle:EntrepriseService')->findOneById($id);
        
        if(!$EntrepriseService){
            if($this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
                return $this->redirectToRoute('entrepriseservice_entreprise');
            } else {
                //ADMIN
               return $this->redirectToRoute('adminentreprise_index');
            }
        }
        $EntrepriseService= $EntrepriseService[0] ;
        
        //Controle User ETR
        if($this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
            $EntrepriseRequest=$EntrepriseService->getEntreprise();
             
            $userEntreprise=$em->getRepository('AppBundle:AdminEntreprise')->findOneByUser($user)[0];
            $EntrepriseuUser=$userEntreprise->getEntreprise();
            
            if($EntrepriseuUser!=$EntrepriseRequest){
               return $this->redirectToRoute('entrepriseservice_entreprise');
            }
        }
        
        //SUITE
        $dateDepot=date('d/m/Y');
        $anneeEncours=date('Y');
        $anneePrecedt=$anneeEncours-1;
        $isDepotanneePrecedt=FALSE;
        $DepotanneePrecedt = $em->getRepository('AppBundle:CourrierEntreprise')->findByServiceAnneePeriode($EntrepriseService, $anneePrecedt.'%');
        if($DepotanneePrecedt){
            $isDepotanneePrecedt=TRUE;
        }
        
        //Contole Affichage Annee Pre et Act
        $anneeRequest=$request->get('annee');
        $anneeSearch=$anneeEncours;
        
        if($anneeRequest==($anneeEncours-1)){
          $anneeSearch=$anneeRequest;
          if(!$isDepotanneePrecedt){
             return $this->redirectToRoute('entrepriseservice_entreprise');
          }
        }elseif ($anneeRequest==NULL) {
             $anneeSearch=$anneeEncours; 
        } else {
             //var_dump('3');          die();
              $anneeSearch=$anneeEncours; 
             //return $this->redirectToRoute('courrierentreprise_index',array('id'=>$id));
        }
        //findByServiceAnneePeriode($service, $annee)
        $courrierEntreprises = $em->getRepository('AppBundle:CourrierEntreprise')->findByServiceAnneePeriode($EntrepriseService, $anneeSearch.'%');

        return $this->render('courrierentreprise/index.html.twig', array(
            'courrierEntreprises' => $courrierEntreprises,
            'user' => $user,
            'EntrepriseService' => $EntrepriseService,
            'dateDepot' => $dateDepot,
            'anneeEncours' => $anneeEncours,
            'anneeSearch' => $anneeSearch,
            'anneeRequest' => $anneeRequest,
            'isDepotanneePrecedt' => $isDepotanneePrecedt,
        ));
    }
    
    /**
     * Lists all courrierEntreprise entities.
     * Acces Liste Docs
     * @Route("/documents", name="courrierentreprise_documents")
     */
    public function documentsAction(Request $request)
    {
        //Contole profil
       if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') && !$this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
           return $this->redirectToRoute('homepage');
        }
        $user=$this->getUser();
        $service = $request->get('service');
        $periode = $request->get('periode');
        $categ = $request->get('categ');
        
        $em = $this->getDoctrine()->getManager(); 
        $EntrepriseService = $em->getRepository('AppBundle:EntrepriseService')->findOneById($service);
        
        if(!$EntrepriseService){
            if($this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
                return $this->redirectToRoute('entrepriseservice_entreprise');
            } else {
                //ADMIN
               return $this->redirectToRoute('adminentreprise_index');
            } 
        }
        $EntrepriseService= $EntrepriseService[0] ;
        
        //Integrite Acces
        if($this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
            
            $EntrepriseRequest=$EntrepriseService->getEntreprise();
            $userEntreprise=$em->getRepository('AppBundle:AdminEntreprise')->findOneByUser($user)[0];
            $EntrepriseuUser=$userEntreprise->getEntreprise();
            if($EntrepriseuUser!=$EntrepriseRequest){
               return $this->redirectToRoute('entrepriseservice_entreprise');
            }
//            elseif ($EntrepriseService->getDroitinout()!='Entreprise') {
//                
//            }
        } else {
             if($EntrepriseService->getDroitinout()!='Prestataire'){
                  return $this->redirectToRoute('courrierentreprise_index', array('id'=>$service));
             }
        }
        
        
        //Les courriers á afficher
        $isLier=FALSE;
        $isNoLier=FALSE;
        
        $courrierEntreprises=NULL;
        if($categ==1){
            $courrierEntreprises = $em->getRepository('AppBundle:CourrierEntreprise')->findByServicePeriodeNotNull($EntrepriseService, $periode.'%');
            $isLier=TRUE;
            
            $courrierTest2=$em->getRepository('AppBundle:CourrierEntreprise')->findByServicePeriodeNull($EntrepriseService, $periode.'%');
            if($courrierTest2){
                 $isNoLier=TRUE;
            }
           
        }elseif ($categ==2) {
            $courrierEntreprises = $em->getRepository('AppBundle:CourrierEntreprise')->findByServicePeriodeNull($EntrepriseService, $periode.'%');
            $isNoLier=TRUE;
            
            $courrierTest1=$em->getRepository('AppBundle:CourrierEntreprise')->findByServicePeriodeNotNull($EntrepriseService, $periode.'%');
            if($courrierTest1){
                 $isLier=TRUE; 
            }
           
        } else {
           $courrierEntreprises = $em->getRepository('AppBundle:CourrierEntreprise')->findByServicePeriode($EntrepriseService, $periode.'%'); 
           
           $courrierTest1=$em->getRepository('AppBundle:CourrierEntreprise')->findByServicePeriodeNotNull($EntrepriseService, $periode.'%');
           if($courrierTest1){
                $isLier=TRUE; 
           }
           $courrierTest2=$em->getRepository('AppBundle:CourrierEntreprise')->findByServicePeriodeNull($EntrepriseService, $periode.'%');
           if($courrierTest2){
                $isNoLier=TRUE;
           }
        }
        
        if(!$courrierEntreprises){
             return $this->redirectToRoute('courrierentreprise_index',array('id'=>$service));
        }
        
        return $this->render('courrierentreprise/liste.html.twig', array(
            'courrierEntreprises' => $courrierEntreprises,
            'user' => $user,
            'EntrepriseService' => $EntrepriseService,
            'periode' => $periode,
            'categ' => $categ,
            'isLier' =>$isLier,
            'isNoLier' =>$isNoLier
        ));
        
       // var_dump('$EntrepriseuUser');        die();
    }

    /**
     * Creates a new courrierEntreprise entity.
     *
     * @Route("/new", name="courrierentreprise_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        //Contole profil
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') && !$this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
           return $this->redirectToRoute('homepage');
        }
        
        $user=$this->getUser();
        $em = $this->getDoctrine()->getManager();
        $courrierEntreprise = new Courrierentreprise();
        
        $naturedoc = $request->get('naturedoc');
          
        $dateMS=date('dmY');
        $service=$request->get('service');
        $EntrepriseService = $em->getRepository('AppBundle:EntrepriseService')->findOneById($service);
        if($EntrepriseService){
            $EntrepriseService= $EntrepriseService[0] ;
        }

        //Recuperation du Fichier á Uploaader
        //Nom origianal et Nom dans le repertoire
        $media = $request->files->get('file');
        if(!$media){
            return $this->redirectToRoute('courrierentreprise_index');
        }
         //return new Response($html);
        $originalFilename = pathinfo($media->getClientOriginalName(), PATHINFO_FILENAME);      
        $nom=$EntrepriseService->getEntreprise()->getNom();
        $nom= str_replace(' ', '', $nom);
        $nom = strtolower($nom);
        $dateEssai=$EntrepriseService->getEntreprise()->getEssai();
        
        //
        $newFilename = $nom.'-'.substr($dateMS, 2).'-'.uniqid().'.'.$media->guessExtension();    
        $nomPDF=$originalFilename.'.'.$media->guessExtension();
       
        //Copie du fichier dans le repertoire documents_directory
        $repUpload=$this->getParameter('documents_directory');
        $reference= substr($nomPDF, 0, strlen($nomPDF)-4);
        $media->move($repUpload, $newFilename);
        
        //Instatiation DOC
        //
        //$entrepriseservice
         $courrierEntreprise->setEntrepriseservice($EntrepriseService);
        
        $clientIDJson=0;
        //$accesreferenceservice
        $accesreferenceservice = $em->getRepository('AppBundle:AccesReferenceService')->findByServiceReference($service, $reference);
        if($accesreferenceservice){
            $accesreferenceservice=$accesreferenceservice[0];
            $courrierEntreprise->setAccesreferenceservice($accesreferenceservice);
            $clientIDJson=$accesreferenceservice->getUsager()->getId();
        }
        
        //$pathPDF
        $courrierEntreprise->setPathPDF($newFilename);
        
        //$nomPDF    
        $courrierEntreprise->setNomPDF($nomPDF);
        
        //$volume
     
       //Determination Nombre de Plis
        $file= $repUpload.$newFilename;
        $nbrPages=$this->getPDFPages($file);
        $modulo=$nbrPages%4;
        $plis=0;
        if($modulo!=0){
            $plis=   ($nbrPages-$modulo)/4 + 1;
        } else {
            $plis=   $nbrPages/4;
        }
        $courrierEntreprise->setVolume($plis);
        
        //Plage 
         $courrierEntreprise->setPage($nbrPages);
                
        //$naturedoc
        if($EntrepriseService->getService()->getService()=='Documents Salarié'){
          $courrierEntreprise->setNature($naturedoc);
        }
       
        
        //$date
        $datedepot=new \DateTime('now');
        $courrierEntreprise->setDate($datedepot);
        
        if($datedepot>$dateEssai){
                $courrierEntreprise->setEssai('non');
        } else {
             $courrierEntreprise->setEssai('oui');
        }
        
        
        //$profil
        if($this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
            $courrierEntreprise->setProfil('Client');
        } else {
            $courrierEntreprise->setProfil('Admin');
        }
        
        //lue
        $courrierEntreprise->setLue('non');
        
        $em->persist($courrierEntreprise);
        $em->flush();
        
        //Json Return For Real Time
        $courrIDJson=$courrierEntreprise->getId();
        $serviceJson=$courrierEntreprise->getEntrepriseservice()->getService()->getService();
        $entreprJson=$courrierEntreprise->getEntrepriseservice()->getEntreprise()->getNom();
        $dateenvJson=$courrierEntreprise->getDate()->format('d/m/Y');
        $pathPDFJson=$courrierEntreprise->getPathPDF();
        $routeAppJson=$this->router->generate('login');
        $natureJson=$courrierEntreprise->getNature(); 
        $actionJson='envoyer';
        $routeDocJson=$this->router->generate('courrierentreprise_show', array('id'=>$courrIDJson));
        
        
        
        return new JsonResponse(array(
            'courrIDJson' => $courrIDJson,
            'serviceJson' => $serviceJson,
            'entreprJson' => $entreprJson,
            'dateenvJson' => $dateenvJson,
            'pathPDFJson' => $pathPDFJson,
            'clientIDJson' => $clientIDJson,
            'routeAppJson' => $routeAppJson,
            'routeDocJson' => $routeDocJson,
            'natureJson' => $natureJson,
             'actionJson' => $actionJson,
            ));
    }

    /**
     * Finds and displays a courrierEntreprise entity.
     *
     * @Route("/{id}", name="courrierentreprise_show")
     * @Method("GET")
     */
    public function showAction(Request $request, CourrierEntreprise $courrierEntreprise)
    {
        //Contole profil
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') && !$this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE') && !$this->get('security.authorization_checker')->isGranted('ROLE_COFFRE')){
           return $this->redirectToRoute('homepage');
        }
        
        $em = $this->getDoctrine()->getManager();
        $user=$this->getUser();
        //$periode = $request->get('periode');

        //Controle
        if($this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
            //Entreprise User
            $userEntreprise=$em->getRepository('AppBundle:AdminEntreprise')->findOneByUser($user)[0];
            $EntrepriseuUser=$userEntreprise->getEntreprise();
            //Entreprise Request
            $EntrepriseRequest=$courrierEntreprise->getEntrepriseservice()->getEntreprise();
            if($EntrepriseuUser!=$EntrepriseRequest){
               return $this->redirectToRoute('entrepriseservice_entreprise');
            }
            
        } else if($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            if($courrierEntreprise->getEntrepriseservice()->getDroitinout()!='Admin'){
                  return $this->redirectToRoute('courrierentreprise_index', array('id'=>$courrierEntreprise->getEntrepriseservice()->getId()));
            }
        } else {
            if($courrierEntreprise->getAccesreferenceservice()!=NULL){
                if($courrierEntreprise->getAccesreferenceservice()->getUsager()!=$user){
                     return $this->redirectToRoute('user_boite');
                }
                //Signe que la lettre est lue
                $courrierEntreprise->setLue('oui');
                $em->persist($courrierEntreprise);
                $em->flush();
            } else {
                return $this->redirectToRoute('user_boite');
            }
        }
        
        
       // var_dump($EntrepriseRequest->getNom());        die();
        
        
        
        return $this->render('courrierentreprise/show.html.twig', array(
            'courrierEntreprise' => $courrierEntreprise,
            'user' => $user,
              
        ));
    }
    
    
    
    /**
     * Finds and displays a courrierEntreprise entity.
     *
     * @Route("/result/{id}", name="courrierentrepriseresult_show")
     * @Method("GET")
     */
    public function showResultAction(Request $request, CourrierEntreprise $courrierEntreprise)
    {
        //Contole profil
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') && !$this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE') && !$this->get('security.authorization_checker')->isGranted('ROLE_GUICHET')){
           return $this->redirectToRoute('homepage');
        }
        
        $em = $this->getDoctrine()->getManager();
        $user=$this->getUser();
        //$periode = $request->get('periode');
        
        $reference= $request->get('rechercheDocRef');
        $service =  $request->get('rechercheDocIdServ');

        //Controle
        if($this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
            //Entreprise User
            $userEntreprise=$em->getRepository('AppBundle:AdminEntreprise')->findOneByUser($user)[0];
            $EntrepriseuUser=$userEntreprise->getEntreprise();
            //Entreprise Request
            $EntrepriseRequest=$courrierEntreprise->getEntrepriseservice()->getEntreprise();
            if($EntrepriseuUser!=$EntrepriseRequest){
               return $this->redirectToRoute('entrepriseservice_entreprise');
            }
            
        } else if($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            if($courrierEntreprise->getEntrepriseservice()->getDroitinout()!='Admin'){
                  return $this->redirectToRoute('courrierentreprise_index', array('id'=>$courrierEntreprise->getEntrepriseservice()->getId()));
            }
        } else {
            
            $guichentreprise = $em->getRepository('AppBundle:Guichet')->findOneByUser($user)[0];
            $EntrepriseuUser=$guichentreprise->getAgence()->getEntreprise();
            
             //Entreprise Request
            $EntrepriseRequest=$courrierEntreprise->getEntrepriseservice()->getEntreprise();
            if($EntrepriseuUser!=$EntrepriseRequest){
               return $this->redirectToRoute('entrepriseservice_entreprise');
            }
            if($courrierEntreprise->getEntrepriseservice()->getDroitguichet()!='Visualiser'){
                return $this->redirectToRoute('entrepriseservice_entreprise');
            }
            
        }
        
        
       // var_dump($EntrepriseRequest->getNom());        die();
        
        
        
        return $this->render('courrierentreprise/showResult.html.twig', array(
            'courrierEntreprise' => $courrierEntreprise,
            'user' => $user,
            'reference' => $reference,
            'service' => $service,
              
        ));
    }
    
    /**
     *
     * @Route("/telechargement/note/", name="telechargement_note")
     * @Method("GET")
     * 
    */
    public function telechargementNoteAction(Request $request)
    {
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_COFFRE')){
           return $this->redirectToRoute('homepage');
        }
        
        $id =  $request->get('id');
        $action =  $request->get('action');
        $em = $this->getDoctrine()->getManager();
        $courrierEntreprise = $em->getRepository('AppBundle:CourrierEntreprise')->findOneById($id);
        
        if($courrierEntreprise){
            if ($action=='@cruser$ab') {
                $courrierEntreprise=$courrierEntreprise[0];
                $courrierEntreprise->setLue('oui');
                $em->persist($courrierEntreprise);
                $em->flush();
                return new JsonResponse([
                   'message'=>1,
                   'id'=>$id,
                ]);
            }  
        }
        
        return new JsonResponse([
           'message'=>0,
           'id'=>$id,
        ]);
        
    }

    
    
    /**
     * Deletes a courrierEntreprise entity.
     *
     * @Route("/delete/{id}", name="courrierentreprise_supprimer")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, CourrierEntreprise $courrierEntreprise)
    {
        //var_dump('$courrierEntreprise');        die();
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') && !$this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
           return $this->redirectToRoute('homepage');
        }
        
        $id=$courrierEntreprise->getEntrepriseservice()->getId();
        $em = $this->getDoctrine()->getManager();
         $user=$this->getUser();
        //Controle
        if($this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
            //Entreprise User
            $userEntreprise=$em->getRepository('AppBundle:AdminEntreprise')->findOneByUser($user)[0];
            $EntrepriseuUser=$userEntreprise->getEntreprise();
            //Entreprise Request
            $EntrepriseRequest=$courrierEntreprise->getEntrepriseservice()->getEntreprise();
            if($EntrepriseuUser!=$EntrepriseRequest){
               return $this->redirectToRoute('entrepriseservice_entreprise');
            } else {
                if($courrierEntreprise->getEntrepriseservice()->getDroitinout()!='Entreprise'){
                  return $this->redirectToRoute('courrierentreprise_index', array('id'=>$courrierEntreprise->getEntrepriseservice()->getId()));
             }
            }
            
        } else {
            if($courrierEntreprise->getEntrepriseservice()->getDroitinout()!='Admin'){
                  return $this->redirectToRoute('courrierentreprise_index', array('id'=>$courrierEntreprise->getEntrepriseservice()->getId()));
             }
        }
        
         //Json Return For Real Time
        $clientIDJson=0;
        if($courrierEntreprise->getAccesreferenceservice()!=NULL){ 
            $clientIDJson=$courrierEntreprise->getAccesreferenceservice()->getUsager()->getId();
        }
        $courrIDJson=$courrierEntreprise->getId();
        $actionJson='supprimer';
        
        $repUpload=$this->getParameter('documents_directory');
        unlink($repUpload.$courrierEntreprise->getPathPDF());
        $em->remove($courrierEntreprise);
        $em->flush();
        
        
       
        
        
        $arrayCollection[] = array(
                'courrIDJson' => $courrIDJson,
                'clientIDJson' => $clientIDJson,
                'actionJson' => $actionJson
        );
        return new JsonResponse($arrayCollection);
        
//        //substr("abcdef", 0, -1)
//        $periode = $request->get('periode');
//        $categ = $request->get('categ');
//        return $this->redirectToRoute('courrierentreprise_documents',array(
//            'periode'=>$periode,
//            'service'=>$id,
//            'categ'=>$categ
//        ));
    }

    /**
     * Deletes a courrierEntreprise entity.
     * Suppression Liste docs
     * @Route("/supprimer/courriers", name="courrierentreprise_delete")
     */
    public function supprimerAction(Request $request)
    {        //var_dump('k');die();
        //Contole profil
       if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') && !$this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
           return $this->redirectToRoute('homepage');
        }
        $user=$this->getUser();
        $service = $request->get('service');
        $periode = $request->get('periode');
        $categ = $request->get('categ');
        
        $em = $this->getDoctrine()->getManager(); 
        $EntrepriseService = $em->getRepository('AppBundle:EntrepriseService')->findOneById($service);
        
        if(!$EntrepriseService){
            if($this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
                return $this->redirectToRoute('entrepriseservice_entreprise');
            } else {
                //ADMIN
               return $this->redirectToRoute('adminentreprise_index');
            }
        }
        $EntrepriseService= $EntrepriseService[0] ;
        
        if($this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
            //Integrite Acces
            $EntrepriseRequest=$EntrepriseService->getEntreprise();
            $userEntreprise=$em->getRepository('AppBundle:AdminEntreprise')->findOneByUser($user)[0];
            $EntrepriseuUser=$userEntreprise->getEntreprise();
            if($EntrepriseuUser!=$EntrepriseRequest){
               return $this->redirectToRoute('entrepriseservice_entreprise');
            } else {
                    if($EntrepriseService->getDroitinout()!='Client'){
                  return $this->redirectToRoute('courrierentreprise_index', array('id'=>$EntrepriseService->getId()));
                } 
            }
        } else {
            if($EntrepriseService->getDroitinout()!='Prestataire'){
                  return $this->redirectToRoute('courrierentreprise_index', array('id'=>$EntrepriseService->getId()));
            }
        }
        
        
        //Les courriers á supprimer
        //$courrierEntreprises = $em->getRepository('AppBundle:CourrierEntreprise')->findByServicePeriode($EntrepriseService, $periode.'%');

        $courrierEntreprises=NULL;
        if($categ==1){
            $courrierEntreprises = $em->getRepository('AppBundle:CourrierEntreprise')->findByServicePeriodeNotNull($EntrepriseService, $periode.'%');
        }elseif ($categ==2) {
            $courrierEntreprises = $em->getRepository('AppBundle:CourrierEntreprise')->findByServicePeriodeNull($EntrepriseService, $periode.'%');
        } else {
           $courrierEntreprises = $em->getRepository('AppBundle:CourrierEntreprise')->findByServicePeriode($EntrepriseService, $periode.'%'); 
        }
        $repUpload=$this->getParameter('documents_directory');
        
        //Json Return For Real Time
        $clientIDJson=0;
        $actionJson='supprimer';
        $arrayCollection = array();

        foreach($courrierEntreprises as $key => $value)
        {
            //Initialisation pour Real Time
            if($courrierEntreprises[$key]->getAccesreferenceservice()!=NULL){ 
                $clientIDJson=$courrierEntreprises[$key]->getAccesreferenceservice()->getUsager()->getId();
            }
            
            $arrayCollection[] = array(
                'courrIDJson' => $courrierEntreprises[$key]->getId(),
                'clientIDJson' => $clientIDJson,
                'actionJson' => $actionJson
            );
            $clientIDJson=0;
            //Suppression doc du Serveur
            unlink($repUpload.$courrierEntreprises[$key]->getPathPDF());
            //Suppression doc du BDD
            $em->remove($courrierEntreprises[$key]);
            $em->flush();
        }
        
         //var_dump(count($courrierEntreprises));         die();
         return new JsonResponse($arrayCollection);
        //return $this->redirectToRoute('courrierentreprise_index',array('id'=>$service));
    }
    
    /**
     * @Route("/depot/recherche", name="recherche_depotscoffre")
     * @Method({"GET, POST"})
     */
    public function RechercheDocsAction(Request $request)
    {        //var_dump('$request');        die();
        //On evite l'execution de cette fonction pour user connecte
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_GUICHET') && !$this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
           return $this->redirectToRoute('homepage');
        }
        $reference= $request->get('rechercheDocRef');
        $service =  $request->get('rechercheDocIdServ');
          //var_dump($service);        die();
        
        $user=$this->getUser();
        $em = $this->getDoctrine()->getManager(); 
        $EntrepriseService = $em->getRepository('AppBundle:EntrepriseService')->findOneById($service);
        if(!$EntrepriseService){
           return $this->redirectToRoute('entrepriseservice_entreprise'); 
        }
        $EntrepriseService=$EntrepriseService[0];
        
        $courriers=$em->getRepository('AppBundle:CourrierEntreprise')->findByServiceReference($EntrepriseService, $reference.'.pdf');
       
        $Coffres = $em->getRepository('AppBundle:AccesReferenceService')->findByServiceReference($service, $reference);
        
       return $this->render('courrierentreprise/result.html.twig', array(
            'courriers' => $courriers,
            'user' => $user,
            'EntrepriseService' => $EntrepriseService,  
            'reference' => $reference,
            'service' => $service,
            'Coffres' => $Coffres
        ));
    }
    
    

    /**
     * Creates a form to delete a courrierEntreprise entity.
     *
     * @param CourrierEntreprise $courrierEntreprise The courrierEntreprise entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(CourrierEntreprise $courrierEntreprise)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('courrierentreprise_delete', array('id' => $courrierEntreprise->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
    
    // Make a function for convenience 
function getPDFPages($document)
{   //Remote /usr/bin/pdfinfo /local
    //$cmd = "/usr/bin/pdfinfo";           // Linux
     $cmd = "/usr/bin/pdfinfo";           // Linux
    //$cmd = "C:\\path\\to\\pdfinfo.exe";  // Windows

    // Parse entire output
    // Surround with double quotes if file name has spaces
    exec("$cmd \"$document\"", $output);

    // Iterate through lines
    $pagecount = 0;
    foreach($output as $op)
    {
        // Extract the number
        if(preg_match("/Pages:\s*(\d+)/i", $op, $matches) === 1)
        {
            $pagecount = intval($matches[1]);
            break;
        }
    }

    return $pagecount;
}
}
