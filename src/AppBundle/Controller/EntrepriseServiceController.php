<?php

namespace AppBundle\Controller;

use AppBundle\Entity\EntrepriseService;
use AppBundle\Entity\Entreprise;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Entrepriseservice controller.
 *
 * @Route("entrepriseservice")
 */
class EntrepriseServiceController extends Controller
{
    /**
     * Lists all entrepriseService entities.
     *
     * @Route("/{id}", name="entrepriseservice_index")
     * @Method("GET")
     */
    public function indexAction(Request $request, Entreprise $entreprise)
    {   
        //Bloque l'execution
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
           return $this->redirectToRoute('homepage');
        }
        $user=$this->getUser();
        //Listing
        $em = $this->getDoctrine()->getManager();
        $entrepriseServices = $em->getRepository('AppBundle:EntrepriseService')->findByEntreprise($entreprise);
        
        //Creating
        $entrepriseService = new Entrepriseservice();
        $entrepriseService->setEntreprise($entreprise);
        $form = $this->createForm('AppBundle\Form\EntrepriseServiceType', $entrepriseService);
        $form->handleRequest($request);
        
        
//       //Poids Unitaire
//            $poidsTarif=120;
//            $nbrTarif=4;
//            $poidsunitaire=$poidsTarif*$nbrTarif;

       
        
        
        //Vue Listing
        return $this->render('entrepriseservice/index.html.twig', array(
            'entrepriseServices' => $entrepriseServices,
            'form' => $form->createView(),
            'entreprise' => $entreprise,
            'user' => $user, 
        ));
    }
    


    /**
     * Displays a form to edit an existing entrepriseService entity.
     *
     * @Route("/{id}/edit", name="entrepriseservice_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, EntrepriseService $entrepriseService)
    {
        //Bloque l'execution
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
           return $this->redirectToRoute('homepage');
        }
        
        $user=$this->getUser();
        $em = $this->getDoctrine()->getManager();
         //On recupere l'entreprise de user createur
        $userEntreprise=$em->getRepository('AppBundle:AdminEntreprise')->findOneByUser($user)[0];
        $EntrepriseuUser=$userEntreprise->getEntreprise();
        
        $EntrepriseRequest=$entrepriseService->getEntreprise();
        if($EntrepriseuUser!=$EntrepriseRequest){
            return $this->redirectToRoute('entrepriseservice_entreprise');
        }
        
//        if($entrepriseService->getService()->getService()!='Documents Salarié'){
//           
//
//            $droitguichet=$request->get('droitguichet');
//            $entrepriseService->setDroitguichet($droitguichet);
//        } 
        
//         $inputoutput=$request->get('inputoutput');
//         $entrepriseService->setDroitinout($inputoutput);
        
        
        $stockage=$request->get('stockage');
        $entrepriseService->setStockage($stockage);
        
        //var_dump($float_value);die();
         //var_dump($punitaire);die();
        
       
        $em->flush();  
        
        return $this->redirectToRoute('entrepriseservice_entreprise');

        }
    
    

    /**
     * Deletes a entrepriseService entity.
     *
     * @Route("/delete/{id}", name="entrepriseservice_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, EntrepriseService $entrepriseService)
    {
        //Bloque l'execution
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
           return $this->redirectToRoute('homepage');
        }
        $user=$this->getUser();
        $em = $this->getDoctrine()->getManager();
         //On recupere l'entreprise de user createur
        $userEntreprise=$em->getRepository('AppBundle:AdminEntreprise')->findOneByUser($user)[0];
        $EntrepriseuUser=$userEntreprise->getEntreprise();
        
        $EntrepriseRequest=$entrepriseService->getEntreprise();
        if($EntrepriseuUser!=$EntrepriseRequest){
            return $this->redirectToRoute('entrepriseservice_entreprise');
        }
        
        try {

              $em->remove($entrepriseService);
              $em->flush();
        } catch (\Doctrine\DBAL\DBALException $ex) {

        }
       
        return $this->redirectToRoute('entrepriseservice_entreprise');
    }

    
}
