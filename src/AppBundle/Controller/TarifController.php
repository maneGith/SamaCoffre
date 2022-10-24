<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Tarif;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Tarif controller.
 *
 * @Route("tarif")
 */
class TarifController extends Controller
{
    /**
     * Lists all tarif entities.
     *
     * @Route("/", name="tarif_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        //On evite l'execution de cette fonction pour user connecte
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            return $this->redirectToRoute('homepage');          
        }
        
        $em = $this->getDoctrine()->getManager();
        $user=$this->getUser();
        $tarifs = $em->getRepository('AppBundle:Tarif')->findByOrdre();
        
        
        $tarif = new Tarif();
        $form = $this->createForm('AppBundle\Form\TarifType', $tarif);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Controle de Saisie
            if($tarif->getService()=='0'){
                  return $this->redirectToRoute('tarif_index');
            }
            
            $float_value = floatval($tarif->getPrixunitaire());
            if($float_value==0){
                 return $this->redirectToRoute('tarif_index');
            }
            $tarif->setPrixunitaire($float_value);
            
            //Cout Stockage
//            $coutstockage=$float_value*30/100*3;
            $coutstockage=100;
            $tarif->setCoutstockage($coutstockage);
            
            //Cout Traitement
             $couttraitement=25;
            $tarif->setCouttraitement($couttraitement);
            
            //var_dump($float_value);die();
            
            $service=$tarif->getService();
            $servicedeja = $em->getRepository('AppBundle:Tarif')->findByService($service);
            if($servicedeja){
               return $this->redirectToRoute('tarif_index');
            }
            
            //Reference
            if($tarif->getService()=='Documents Salarié') {
                 $tarif->setReference('Matricule');
            }elseif ($tarif->getService()=='Relevés de Compte' or $tarif->getService()=='Avis de Banque') {
                 $tarif->setReference('NoCompte');
            }elseif ($tarif->getService()=='Factures' or $tarif->getService()=='Contrats') {
                 $tarif->setReference('RefClient');
            }else {
                $tarif->setReference('Référence'); 
            }
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($tarif);
            $em->flush();

            return $this->redirectToRoute('tarif_index');
        }

        return $this->render('tarif/index.html.twig', array(
            'tarifs' => $tarifs,
             'user' => $user,
             'form' => $form->createView(),
        ));
    }

    

    

    /**
     * Displays a form to edit an existing tarif entity.
     *
     * @Route("/edit", name="tarif_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request)
    {
        //On evite l'execution de cette fonction pour user connecte
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            return $this->redirectToRoute('homepage');          
        }
        
        $tarif=$request->get('tarifId');
        $prixunitaire=$request->get('tarifService');
        
        $em = $this->getDoctrine()->getManager();
        $user=$this->getUser();
        $tarif = $em->getRepository('AppBundle:Tarif')->findOneById($tarif);
        
        if($tarif){
            $tarif=$tarif[0];
            $float_value = floatval($prixunitaire);
            if($float_value==0){
                return $this->redirectToRoute('tarif_index');
            }
            //var_dump($float_value);die();
            $tarif->setPrixunitaire($float_value);
            
            //Cout Stockage
            //$coutstockage=$float_value*30/100*3;
             $coutstockage=100;
            $tarif->setCoutstockage($coutstockage);
            
            //Cout Traitement
             $couttraitement=25;
            $tarif->setCouttraitement($couttraitement);
            
            $em->persist($tarif);
            $em->flush();
            return $this->redirectToRoute('tarif_index');  
        } else {
          return $this->redirectToRoute('tarif_index');  
        }
    }

    /**
     * Deletes a tarif entity.
     *
     * @Route("/delete", name="tarif_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request)
    {
        
            //On evite l'execution de cette fonction pour user connecte
            if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
                return $this->redirectToRoute('homepage');          
            }
            
            $id=$request->get('id');
     
        
            $em = $this->getDoctrine()->getManager();
            $tarif = $em->getRepository('AppBundle:Tarif')->findOneById($id)[0];
            $em = $this->getDoctrine()->getManager();
             try {
                  $em->remove($tarif);
                  $em->flush(); 
             } catch (\Doctrine\DBAL\DBALException $ex) {

             }
          
            
            return new JsonResponse([
               'message'=>'success',
            ]);
    }

    
}
