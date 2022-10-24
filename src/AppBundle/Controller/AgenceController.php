<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Agence;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Guichet;
use AppBundle\Entity\User;

/**
 * Agence controller.
 *
 * @Route("agence")
 */
class AgenceController extends Controller
{
    /**
     * Lists all agence entities.
     *
     * @Route("/", name="agence_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
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
        $listeagences = $em->getRepository('AppBundle:Agence')->findAgencesByEntreprise($EntrepriseuUser);
        
        
        
        $agence = new Agence();
        $form = $this->createForm('AppBundle\Form\AgenceType', $agence);
        $form->handleRequest($request);
        
        
        if ($form->isSubmitted() && $form->isValid()) {
            //Retour si Nom Vide
            if(trim($agence->getNom())==''){
                return $this->redirectToRoute('agence_index');
            }
            
            $agence->setEntreprise($EntrepriseuUser);
            $em = $this->getDoctrine()->getManager();
            $em->persist($agence);
            $em->flush();

            return $this->redirectToRoute('agence_index');
        }

        //Pour chaque Agence ces Guichets array('agence'=>'','guichets'=>'');
            $agences=array();
            $agenceGuichets=array();
         
         for ($i=0; $i<count($listeagences); $i++){
            $agenceGuichets['agence']= $listeagences[$i];

            $guichets = $em->getRepository('AppBundle:Guichet')->findGuichetsByAgence($listeagences[$i]);
            $agenceGuichets['guichets'] = $guichets;

            $agences[$i] = $agenceGuichets;
         }

        
        
        //$guichets = $em->getRepository('AppBundle:Guichet')->findGuichetsByAgence($agence);
        
        return $this->render('agence/index.html.twig', array(
            'user' => $user,
            'agences' => $agences,
            'form' => $form->createView(),
        ));
    }

    
    

    /**
     * Displays a form to edit an existing agence entity.
     *
     * @Route("/{id}/edit", name="agence_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Agence $agence)
    { 
        //Bloque l'execution
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
           return $this->redirectToRoute('homepage');
        }
        
        $user=$this->getUser();
        //On compare l'entreprise user createur a celui de l'agence
        //Pour eviter un admin entreprise d'acceder aux infos de l'agence d'une autre entreprise
        $em = $this->getDoctrine()->getManager();
        $userEntreprise=$em->getRepository('AppBundle:AdminEntreprise')->findOneByUser($user)[0];
        $EntrepriseuUser=$userEntreprise->getEntreprise();
        $EntrepriseAgence=$agence->getEntreprise();
        if($EntrepriseuUser!=$EntrepriseAgence){
            return $this->redirectToRoute('agence_index');
        }
        
        $nom = $request->get('nom');

        //Retour si Nom Vide
        if(trim($nom)==''){
            return $this->redirectToRoute('agence_index');
        }

        $agence->setNom($nom);
        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute('agence_index');
       

        
    }

    /**
     * Deletes a agence entity.
     *
     * @Route("/delete/{id}", name="agence_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Agence $agence)
    {
        //Bloque l'execution
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
           return $this->redirectToRoute('homepage');
        }
        
        $user=$this->getUser();
        //On compare l'entreprise user createur a celui de l'agence
        //Pour eviter un admin entreprise d'acceder aux infos de l'agence d'une autre entreprise
        $em = $this->getDoctrine()->getManager();
        $userEntreprise=$em->getRepository('AppBundle:AdminEntreprise')->findOneByUser($user)[0];
        $EntrepriseuUser=$userEntreprise->getEntreprise();
        $EntrepriseAgence=$agence->getEntreprise();
        if($EntrepriseuUser!=$EntrepriseAgence){
            return $this->redirectToRoute('agence_index');
        }
        
       
        try {
                $em = $this->getDoctrine()->getManager();
                $em->remove($agence);
                $em->flush();
                return $this->redirectToRoute('agence_index');
        } catch (\Doctrine\DBAL\DBALException $ex) {
                return $this->redirectToRoute('agence_index');
        }

    }

    /**
     * Creates a form to delete a agence entity.
     *
     * @param Agence $agence The agence entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Agence $agence)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('agence_delete', array('id' => $agence->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
    
    /**
     * 
     * @param type $guichets
     * @param type $guichet
     * @return boolean
     */
    
    public function isGuichet($guichets, $guichet){
        $returValue=FALSE;
        for ($i=0;$i<count($guichets);$i++){
                if ($guichets[$i]->getUser()->getNom()==$guichet) {
                   $returValue=TRUE;
                    break;
                 }
        }
        
        return $returValue;
    }
}
