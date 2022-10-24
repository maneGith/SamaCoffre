<?php

namespace AppBundle\Controller;

use AppBundle\Entity\AccesReferenceService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\EntrepriseService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\RouterInterface;
/**
 * Accesreferenceservice controller.
 *
 * @Route("accesreferenceservice")
 */
class AccesReferenceServiceController extends Controller
{
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }
    
    /**
     * Lists all accesReferenceService entities.
     *
     * @Route("/", name="accesreferenceservice_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_GUICHET') && !$this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
           return $this->redirectToRoute('homepage');
        }

        //EntrepriseService $entrepriseservice;
        //findOneById($id)
        $user=$this->getUser();
        $id =  $request->get('id');
        $action = $request->get('action');
        $em = $this->getDoctrine()->getManager();
        $entrepriseservice = $em->getRepository('AppBundle:EntrepriseService')->findOneById($id);
        if($entrepriseservice){
            $entrepriseservice=$entrepriseservice[0];
        } else {
            return $this->redirectToRoute('entrepriseservice_entreprise');
        }
        $coffres=$em->getRepository('AppBundle:User')->findByCoffres();
        //var_dump(count($coffres));        die();
        
         
        //Entreprise Service Request
        $EntrepriseRequest=$entrepriseservice->getEntreprise();
        $EntrepriseuUser=null;
        $accesReferenceServices=null;
        $guichet=null;
        
        if($this->get('security.authorization_checker')->isGranted('ROLE_GUICHET')){
            //On Cherche Agence User Guichet
            $guichet = $em->getRepository('AppBundle:Guichet')->findOneByUser($user)[0];
            $agence= $guichet->getAgence();
            //ETR GUICHET
            $EntrepriseuUser=$agence->getEntreprise();
            if($EntrepriseuUser!=$EntrepriseRequest){
                return $this->redirectToRoute('entrepriseservice_entreprise');
            }
            if($guichet->getDroitauto()!='oui'){
                 return $this->redirectToRoute('entrepriseservice_entreprise');
            }
            $accesReferenceServices = $em->getRepository('AppBundle:AccesReferenceService')->findByServiceAgence($entrepriseservice, $agence);
        } else {
            //EntrepriseService User
            $userEntreprise=$em->getRepository('AppBundle:AdminEntreprise')->findOneByUser($user)[0];
            $EntrepriseuUser=$userEntreprise->getEntreprise();
            if($EntrepriseuUser!=$EntrepriseRequest){
                return $this->redirectToRoute('entrepriseservice_entreprise');
            }
            $accesReferenceServices = $em->getRepository('AppBundle:AccesReferenceService')->findByService($entrepriseservice);
        }
        
        $accesReferenceService = new Accesreferenceservice();
        $form = $this->createForm('AppBundle\Form\AccesReferenceServiceType', $accesReferenceService);
        $form->handleRequest($request);

//        if ($form->isSubmitted() && $form->isValid()) {
        if($action=='@cruser$ab') {
            //$em = $this->getDoctrine()->getManager();
            //Relier Service
            //Si Bloquee Detour Bloque
            
            //var_dump($entrepriseservice->getEtat());die();
            
            $accesReferenceService->setService($entrepriseservice);
            
            //Relier Usager
            $email =  $request->get('email');
            $usager = $em->getRepository('AppBundle:User')->findOneByEmail($email)[0];
            $accesReferenceService->setUsager($usager);
            
             //Relier Guichet
             if($this->get('security.authorization_checker')->isGranted('ROLE_GUICHET')){
                 
                 $guichet = $em->getRepository('AppBundle:Guichet')->findOneByUser($user)[0];
                 $accesReferenceService->setGuichet($guichet);
             }
             
            //Set Reference 
            $reference =  $request->get('reference');
            $accesReferenceService->setReference($reference);
              
            $em->persist($accesReferenceService);
            $em->flush();
            
            
             //Recherche Docs de ce Service Portant Reference
            $service=$id;
            $reference=$accesReferenceService->getReference();
            $courrierEntreprises = $em->getRepository('AppBundle:CourrierEntreprise')->findByServiceReference($service, $reference.'.pdf');
            $arrayCollection = array();
            
            foreach($courrierEntreprises as $key => $value)
            {
                $courrierEntreprises[$key]->setAccesreferenceservice($accesReferenceService);
                $em->flush();
                
                //Json Return For Real Time
                $courrIDJson=$courrierEntreprises[$key]->getId();
                $serviceJson=$courrierEntreprises[$key]->getEntrepriseservice()->getService();
                $entreprJson=$courrierEntreprises[$key]->getEntrepriseservice()->getEntreprise()->getNom();
                $dateenvJson=$courrierEntreprises[$key]->getDate()->format('d/m/Y');
                $pathPDFJson=$courrierEntreprises[$key]->getPathPDF();
                $clientIDJson=$courrierEntreprises[$key]->getAccesreferenceservice()->getUsager()->getId();
                $routeAppJson=$this->router->generate('login');;
                $actionJson='envoyer';
                $routeDocJson=$this->router->generate('courrierentreprise_show', array('id'=>$courrIDJson));

                $arrayCollection[] = array(
                    'courrIDJson' => $courrIDJson,
                    'serviceJson' => $serviceJson,
                    'entreprJson' => $entreprJson,
                    'dateenvJson' => $dateenvJson,
                    'pathPDFJson' => $pathPDFJson,
                    'clientIDJson' => $clientIDJson,
                    'routeAppJson' => $routeAppJson,
                    'routeDocJson' => $routeDocJson,
                     'actionJson' => $actionJson,
                );
            }
                
            //var_dump(count($courrierEntreprises));die();
            return new JsonResponse($arrayCollection); 

            //return $this->redirectToRoute('accesreferenceservice_index', array('id' => $entrepriseservice->getId()));
        }

        

        return $this->render('accesreferenceservice/index.html.twig', array(
            'accesReferenceServices' => $accesReferenceServices,
            'user' => $user,
            'entrepriseservice' => $entrepriseservice,
            'form' => $form->createView(),
             'coffres' => $coffres
        ));
    }
    
    /**
     * Lists all accesReferenceService entities.
     *
     * @Route("/entreprise", name="accesreferenceservice_refentreprise")
     * @Method("GET")
     */
    public function refentrepriseAction(Request $request)
    {
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
               return $this->redirectToRoute('homepage');
        } 
        
        //var_dump('$request');        die();
        //Determination Entreprise User
        
        //findOneById($id)
        $user=$this->getUser();
        $id =  $request->get('id');
        $em = $this->getDoctrine()->getManager();
        
        //EntrepriseService $entrepriseservice;
        $userEntreprise=$em->getRepository('AppBundle:AdminEntreprise')->findOneByUser($user)[0];
        $EntrepriseuUser=$userEntreprise->getEntreprise();
        
        //Entreprise Service Request
        $entrepriseservice = $em->getRepository('AppBundle:EntrepriseService')->findOneById($id);
        if($entrepriseservice){
            $entrepriseservice=$entrepriseservice[0];
        } else {
            return $this->redirectToRoute('entrepriseservice_entreprise');
        }
        
        $EntrepriseRequest=$entrepriseservice->getEntreprise();
        if($EntrepriseuUser!=$EntrepriseRequest){
            return $this->redirectToRoute('entrepriseservice_entreprise');
        }
        
        //var_dump($EntrepriseRequest->getNom());        die();
        
        $accesReferenceServices = $em->getRepository('AppBundle:AccesReferenceService')->findByService($entrepriseservice);
        
         return $this->render('accesreferenceservice/liste.reference.entreprise.html.twig', array(
            'accesReferenceServices' => $accesReferenceServices,
            'user' => $user,
            'entrepriseservice' => $entrepriseservice,
        ));
      
    }
    
    
    
    
    
    /**
     * @Route("/controle", name="accces_controle")
     * @Method({"GET"})
     */
    public function AcccesControleAction(Request $request)
    {        //var_dump('$request');        die();
        //On evite l'execution de cette fonction pour user connecte
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_GUICHET') && !$this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
           return $this->redirectToRoute('homepage');
        }
        
        $action = $request->get('action');
        $service =  $request->get('service');
        $reference =  $request->get('reference');
        $email =  $request->get('email');
        
        $message=0;
        // 1 Compte n'existe pas, 2 compte email non prevue pour ce service
        // 3 Refrece deja attribue a pour ce service
        //Controle Action Normal Request
        if($action!='@cruser$ab') {
          return $this->redirectToRoute('homepage');   
        }
        
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->findOneByEmail($email);
         
       
        if(!$user){
            $message=1; 
        } else {
           $user=$user[0]; 
           if($user->getRoles()[0]!='ROLE_COFFRE'){
              $message=2;  
           } else {
               //Tester sil ya attribution  pour cette ref et ce servive
                 $chekerService = $em->getRepository('AppBundle:AccesReferenceService')->findByServiceReference($service, $reference);
                 if($chekerService){
                      $message=3;  
                 }
           }
        }
            
        return new JsonResponse([
                'message'=>$message,
        ]); 
    }
    
    
    /**
     * @Route("/search", name="accces_search")
     * @Method({"GET"})
     */
    public function AcccesSearchAction(Request $request)
    { 
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_GUICHET') && !$this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
           return $this->redirectToRoute('homepage');
        }
        
        $service =  $request->get('service');
        $reference =  $request->get('reference');
        
        //Tester sil ya attribution  servive sur ref
        $em = $this->getDoctrine()->getManager();
        $chekerServiceUsager = $em->getRepository('AppBundle:AccesReferenceService')->findByServiceReference($service, $reference);
        if($chekerServiceUsager){
            return $this->redirectToRoute('accesreferenceservice_show', array('id' => $chekerServiceUsager[0]->getId()));
            // var_dump('yes');die();
        } else {
            //var_dump('non');die();
            if($this->get('security.authorization_checker')->isGranted('ROLE_GUICHET')){
                return $this->redirectToRoute('accesreferenceservice_index', array('id' => $service));
            } else {
                return $this->redirectToRoute('accesreferenceservice_index', array('id' => $service));
            }
            
        }
        
    }
    
    
     /**
     * Finds and displays a accesReferenceService entity.
     *
     * @Route("/{id}", name="accesreferenceservice_show")
     * @Method("GET")
     */
    public function showAction(AccesReferenceService $accesReferenceService)
    {
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_GUICHET') && !$this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
           return $this->redirectToRoute('homepage');
        }
        
        
       
        
        $user=$this->getUser();
        $em = $this->getDoctrine()->getManager();
        
          //User ETR
         $EntrepriseuUser='';
         
        if($this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
            $userEntreprise=$em->getRepository('AppBundle:AdminEntreprise')->findOneByUser($user)[0];
            $EntrepriseuUser=$userEntreprise->getEntreprise();
        } else {
            $userEntreprise=$em->getRepository('AppBundle:Guichet')->findOneByUser($user)[0];
            $EntrepriseuUser=$userEntreprise->getAgence()->getEntreprise();
        }
        //ETR Request
         $EntrepriseRequest=$accesReferenceService->getService()->getEntreprise();
        
         //var_dump($EntrepriseRequest->getNom());         die();
         
        if($EntrepriseuUser!=$EntrepriseRequest){
            return $this->redirectToRoute('entrepriseservice_entreprise');
        }
        
        $deleteForm = $this->createDeleteForm($accesReferenceService);

         return $this->render('accesreferenceservice/show.html.twig', array(
            'user' => $user,
            'accesReferenceService' => $accesReferenceService,
            'delete_form' => $deleteForm->createView(),
        ));
    }


    /**
     * Deletes a accesReferenceService entity.
     *
     * @Route("/delete/{id}", name="accesreferenceservice_delete")
     */
    public function deleteAction(Request $request, AccesReferenceService $accesReferenceService)
    {
        
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
           return $this->redirectToRoute('homepage');
        }
        
        $user=$this->getUser();
        $em = $this->getDoctrine()->getManager();
        
          //User ETR
        $userEntreprise=$em->getRepository('AppBundle:AdminEntreprise')->findOneByUser($user)[0];
        $EntrepriseUser=$userEntreprise->getEntreprise();
        
         //ETR Request
        $EntrepriseRequest=$accesReferenceService->getService()->getEntreprise();
        

        if($EntrepriseUser!=$EntrepriseRequest){
            return $this->redirectToRoute('entrepriseservice_entreprise');
        }
        
        $action = $request->get('action');
        $clientIDJson=0;
        $actionJson='supprimer';
        $arrayCollection = array();
        if($action=='@cruser$ab') {
            //Oter l'access ref sur docs 
            $courrierEntreprises = $em->getRepository('AppBundle:CourrierEntreprise')->findByReference($accesReferenceService->getId());;
            foreach($courrierEntreprises as $key => $value)
            {
                $clientIDJson=$courrierEntreprises[$key]->getAccesreferenceservice()->getUsager()->getId();
                $arrayCollection[] = array(
                    'courrIDJson' => $courrierEntreprises[$key]->getId(),
                    'clientIDJson' => $clientIDJson,
                    'actionJson' => $actionJson
                );
                $courrierEntreprises[$key]->setAccesreferenceservice(NULL);
                $em->flush();
            }
            //Suppression Autorisation
            $em->remove($accesReferenceService);
            $em->flush();
        }
        return new JsonResponse($arrayCollection); 
    }

    /**
     * Creates a form to delete a accesReferenceService entity.
     *
     * @param AccesReferenceService $accesReferenceService The accesReferenceService entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(AccesReferenceService $accesReferenceService)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('accesreferenceservice_delete', array('id' => $accesReferenceService->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
