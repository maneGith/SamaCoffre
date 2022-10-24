<?php

namespace AppBundle\Controller;

use AppBundle\Entity\AdminEntreprise;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\User;
use AppBundle\ValidateEmailAdress\VerifyEmail;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Adminentreprise controller.
 *
 * @Route("adminentreprise")
 */
class AdminEntrepriseController extends Controller
{
    /**
     * Lists all adminEntreprise entities.
     *
     * @Route("/", name="adminentreprise_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    { 
        //Bloque l'execution
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
           return $this->redirectToRoute('homepage');
        }
        
        $user=$this->getUser();
        $em = $this->getDoctrine()->getManager();
        $adminEntreprises = $em->getRepository('AppBundle:AdminEntreprise')->findByEntreprise();
        
        //Ceation
        $adminEntreprise = new Adminentreprise();
        $form = $this->createForm('AppBundle\Form\AdminEntrepriseType', $adminEntreprise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
                    return $this->redirectToRoute('adminentreprise_index');
            }
            
        }//Fin

        return $this->render('adminentreprise/index.html.twig', array(
            'user' => $user,
            'adminEntreprises' => $adminEntreprises,
            'adminEntreprise' => $adminEntreprise,
            'form' => $form->createView(),
        ));
    }
    
    /**
     * Creates a new adminEntreprise entity.
     *
     * @Route("/new", name="adminentreprise_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $message='Precreation';
        
        $nom = $request->get('nom');
        $adresse = $request->get('adresse');
        $email = $request->get('email');
        $telephone = $request->get('telephone');
        $password = $request->get('password');
        $categorie = $request->get('categorie');
        
        
            
        $action = $request->get('action');
        
        if ($action=='@cruser$ab') {
            
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('AppBundle:User')->findOneByEmail($email);
            if($user){
                $message='EmailUsed';
                return new JsonResponse([
                   'message'=>$message,
                ]);
            }
            
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
                $message='EmailNoExist';
                return new JsonResponse([
                   'message'=>$message,
                ]);
            }
            
            //Tentative Creation
            try {
                $adminEntreprise = new Adminentreprise();
                $date=new \DateTime('now');
                
                //Infos User From Form
                $userForm = new User();
                $userForm->setEmail($email);
                $userForm->setUsername($email);
                $userForm->setTelephone($telephone);
                $userForm->setPassword($password);
                $roles[]='ROLE_ENTREPRISE';
                $userForm->setRoles($roles);
                $userForm->setNom($nom);
                $userForm->setDate($date);
                $userForm->setEnabled(true);
                
                //Infos Entreprise From Form
                $entreprise = new \AppBundle\Entity\Entreprise();
                $entreprise->setNom($nom);
                $entreprise->setAdresse($adresse);
                $entreprise->setDate($date);
                $essai=new \DateTime($this->returnDateFinEssai($date));
                $entreprise->setEssai($essai);
                $entreprise->setCategorie($categorie);
                //Set adminEntreprise
                $adminEntreprise->setUser($userForm);
                $adminEntreprise->setEntreprise($entreprise);
                
                $em->persist($adminEntreprise);
                $em->flush();

                
                $message='Creation';
            } catch (\Doctrine\DBAL\DBALException $ex) {
                $message='Duplication';
            }
        }
        
        return new JsonResponse([
               'message'=>$message,
        ]);
    }

    

    /**
     * Finds and displays a adminEntreprise entity.
     *
     * @Route("/{id}", name="adminentreprise_show")
     * @Method("GET")
     */
    public function showAction(AdminEntreprise $adminEntreprise)
    {
        //Bloque l'execution
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
           return $this->redirectToRoute('homepage');
        }
        $user=$this->getUser();
        $deleteForm = $this->createDeleteForm($adminEntreprise);

        // $date=new \DateTime('now');
        // var_dump($this->returnDateFinEssai($date));die();

        return $this->render('adminentreprise/show.html.twig', array(
            'user' => $user,
            'adminEntreprise' => $adminEntreprise,
            'delete_form' => $deleteForm->createView(),
        ));
    }
    
    /**
     * Finds and displays a adminEntreprise entity.
     *
     * @Route("/entreprise/show", name="adminentreprise_entreprise")
     * @Method("GET")
     */
    public function entrepriseshowAction()
    {        //var_dump('$expression');die();
        //Bloque l'execution
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
           return $this->redirectToRoute('homepage');
        }
        $user=$this->getUser();
        $em = $this->getDoctrine()->getManager();
        $adminEntreprise = $em->getRepository('AppBundle:AdminEntreprise')->findOneByUser($user)[0];
        
        $deleteForm = $this->createDeleteForm($adminEntreprise);

        return $this->render('adminentreprise/entreprise.html.twig', array(
            'user' => $user,
            'adminEntreprise' => $adminEntreprise,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing adminEntreprise entity.
     *
     * @Route("/{id}/edit", name="adminentreprise_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, AdminEntreprise $adminEntreprise)
    { 
        //Bloque l'execution
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
           return $this->redirectToRoute('homepage');
        }
        $em = $this->getDoctrine()->getManager();
        $user=$this->getUser();
        $UseradminEntreprise = $em->getRepository('AppBundle:AdminEntreprise')->findOneByUser($user)[0];
        if($UseradminEntreprise!=$adminEntreprise){
           return $this->redirectToRoute('adminentreprise_entreprise'); 
        }
          
        $deleteForm = $this->createDeleteForm($adminEntreprise);
        $editForm = $this->createForm('AppBundle\Form\AdminEntrepriseType', $adminEntreprise);
        $editForm->handleRequest($request);
        
       
        $admetrBDD = $em->getRepository('AppBundle:AdminEntreprise')->findOneById($adminEntreprise->getId())[0];
        
        
        if ($editForm->isSubmitted()) {
            $emailBDD= $admetrBDD->getUser()->getEmail();
            $idBDD= $admetrBDD->getUser()->getId();
           //var_dump($admetrBDD->getUser()->getEmail());        die();
            //Infos User From Form
            $email = $request->get('email');
            
            $entreprise = trim($adminEntreprise->getEntreprise()->getNom());
            if($entreprise==''){
                return $this->render('adminentreprise/edit.html.twig', array(
                        'user' => $user,
                        'errorused'=>4,
                        'adminEntreprise' => $adminEntreprise,
                        'edit_form' => $editForm->createView(),
                        'delete_form' => $deleteForm->createView(),
                    ));
            }
            
            $adresse = trim($request->get('adresse'));
            if($adresse==''){
                return $this->render('adminentreprise/edit.html.twig', array(
                        'user' => $user,
                        'errorused'=>6,
                        'adminEntreprise' => $adminEntreprise,
                        'edit_form' => $editForm->createView(),
                        'delete_form' => $deleteForm->createView(),
                    ));
            }
            
            if($emailBDD!=$email){
                //Tester si email used findOneByEmailId($email, $id)
                $userRequest = $em->getRepository('AppBundle:User')->findOneByEmailId($email, $idBDD);
                if($userRequest){
                    return $this->render('adminentreprise/edit.html.twig', array(
                        'user' => $user,
                        'errorused'=>1,
                        'adminEntreprise' => $adminEntreprise,
                        'edit_form' => $editForm->createView(),
                        'delete_form' => $deleteForm->createView(),
                    ));
                }
               
            }
            //Test Validation email 
             // Initialize library class
            $mail = new VerifyEmail();
            // Set the timeout value on stream
            $mail->setStreamTimeoutWait(20);
            // Set debug output mode
            $mail->Debug= TRUE; 
            $mail->Debugoutput= 'html'; 
            // Set email address for SMTP request
            $mail->setEmailFrom('from@email.com');
            if(!verifyEmail::validate($email)){
               return $this->render('adminentreprise/edit.html.twig', array(
                        'user' => $user,
                        'errorused'=>2,
                        'adminEntreprise' => $adminEntreprise,
                        'edit_form' => $editForm->createView(),
                        'delete_form' => $deleteForm->createView(),
                )); 
            }elseif (!$mail->check($email)) {
              return $this->render('adminentreprise/edit.html.twig', array(
                        'user' => $user,
                        'errorused'=>3,
                        'adminEntreprise' => $adminEntreprise,
                        'edit_form' => $editForm->createView(),
                        'delete_form' => $deleteForm->createView(),
                ));   
            }
            
            //$telephone= trim($adminEntreprise->getUser()->getTelephone());
             $telephone = $request->get('telephone');
            //var_dump($telephone);            die();
            if(strlen($telephone)<5){
                return $this->render('adminentreprise/edit.html.twig', array(
                        'user' => $user,
                        'errorused'=>5,
                        'adminEntreprise' => $adminEntreprise,
                        'edit_form' => $editForm->createView(),
                        'delete_form' => $deleteForm->createView(),
                ));   
            }
            
        
           
            
            $adminEntreprise->getUser()->setEmail($email);
            $adminEntreprise->getUser()->setUsername($email);
            $adminEntreprise->getUser()->setNom($entreprise);
            
             $categorie = $request->get('categorie');
             $adminEntreprise->getEntreprise()->setCategorie($categorie);
              
            $adminEntreprise->getEntreprise()->setAdresse($adresse);
            //$telephone = $request->get('telephone');
            $adminEntreprise->getUser()->setTelephone($telephone);
            
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('adminentreprise_entreprise');
        }

        return $this->render('adminentreprise/edit.html.twig', array(
            'user' => $user,
            'adminEntreprise' => $adminEntreprise,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a adminEntreprise entity.
     *
     * @Route("/delete/{id}", name="adminentreprise_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, AdminEntreprise $adminEntreprise)
    {
        //Bloque l'execution
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
           return $this->redirectToRoute('homepage');
        }
        $form = $this->createDeleteForm($adminEntreprise);
        $form->handleRequest($request);

        $iderror=$adminEntreprise->getId();
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            try {
                    $em->remove($adminEntreprise);
                    $em->flush();
                    return $this->redirectToRoute('adminentreprise_index');
            } catch (\Doctrine\DBAL\DBALException $ex) {
                return $this->redirectToRoute('adminentreprise_show', array('id' => $iderror));
            }
           
        }

        
    }

    /**
     * Creates a form to delete a adminEntreprise entity.
     *
     * @param AdminEntreprise $adminEntreprise The adminEntreprise entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(AdminEntreprise $adminEntreprise)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('adminentreprise_delete', array('id' => $adminEntreprise->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
    
    /**
     * 
     * @param type $annee
     * @return boolean
     */
    function bissextile($annee) {
	if( (is_int($annee/4) && !is_int($annee/100)) || is_int($annee/400)) {
		return TRUE;
	} else {
		return FALSE;
	}
    }
    
    /**
     * 
     * @param type $date
     * @return string
     */
    function returnDateFinEssai($date){
        $dateFinEssai='';
        
        $anInscris = $date->format('Y');
        $moisInscris = $date->format('m');
        $jourInscris = $date->format('d');
        
        $anFinEssai='';
        $moisFinEssai=0;
        $jourFinEssai=0;
        
         //var_dump($moisInscris);die();
        if($moisInscris =='01' || $moisInscris =='03' || $moisInscris =='05' || $moisInscris =='07' || $moisInscris =='08' || $moisInscris =='10' || $moisInscris =='12'){
            $diffJourMois=31-$jourInscris;
            $anFinEssai=$anInscris;
             
            if($moisInscris =='01'){
                if($diffJourMois==30){
                        $jourFinEssai='31';
                        $moisFinEssai=$moisInscris;
                } else {
                    
                    if($this->bissextile($anInscris)){
                        
                        if($diffJourMois>=1){
                            $jourFinEssai=30-$diffJourMois;
                            $moisFinEssai=$moisInscris+1;
                        } else {
                            $jourFinEssai=1;
                            $moisFinEssai=$moisInscris+2;
                        }
                        
                    } else {
                        
                        if($diffJourMois>=2){
                            $jourFinEssai=30-$diffJourMois;
                            $moisFinEssai=$moisInscris+1;
                        } else {
                            if($diffJourMois==0){
                                $jourFinEssai=2;
                            } else {
                                $jourFinEssai=1;
                            }
                            $moisFinEssai=$moisInscris+2;
                        }
                        
                    }
                }
                
            } else if($moisInscris =='12') {
                if($diffJourMois==30) {
                        $jourFinEssai='31';
                        $moisFinEssai=$moisInscris;
                } else {
                    $jourFinEssai = 30-$diffJourMois; 
                    $moisFinEssai=1;
                    $anFinEssai=$anInscris+1;
                }
               
            } else {
                if($diffJourMois==30) {
                        $jourFinEssai='31';
                        $moisFinEssai=$moisInscris;
                } else {
                    $jourFinEssai = 30-$diffJourMois; 
                    $moisFinEssai=$moisInscris+1;
                    $anFinEssai=$anInscris;
                }
                
            }
          
        } else {
             
            $anFinEssai=$anInscris;
            $moisFinEssai=$moisInscris+1;
            if($moisInscris =='02'){
                if($this->bissextile($anInscris)){
                    $diffJourMois=29-$jourInscris;
                    if($diffJourMois==28) {
                        $jourFinEssai='02';
                    } else {
                        $jourFinEssai = 30-$diffJourMois; 
                    }
                } else {
                    $diffJourMois=28-$jourInscris;
                    if($diffJourMois==27) {
                        $jourFinEssai='03';
                    } else {
                        $jourFinEssai = 30-$diffJourMois; 
                    }
                }
            } else {
                $diffJourMois=30-$jourInscris;
                if($diffJourMois==29) {
                    $jourFinEssai='01';
                } else {
                    $jourFinEssai = 30-$diffJourMois; 
                }
                   
            }
            
        }
        $dateFinEssai=$anFinEssai.'-'.$moisFinEssai.'-'.$jourFinEssai;
        
        return $dateFinEssai;
    }
}
