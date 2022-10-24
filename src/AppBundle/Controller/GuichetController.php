<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Guichet;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\ValidateEmailAdress\VerifyEmail;
use AppBundle\Entity\Agence;
use AppBundle\Entity\User;

/**
 * Guichet controller.
 *
 * @Route("guichet")
 */
class GuichetController extends Controller
{
    /**
     * Creates a new guichet entity.
     *
     * @Route("/new/{id}", name="guichet_new")
     * @Method({"POST"})
     */
    public function newAction(Request $request, Agence $agence)
    {
        //blocage
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
           return $this->redirectToRoute('homepage');
        }
        
        $user=$this->getUser();
        $em = $this->getDoctrine()->getManager();
        
        $em = $this->getDoctrine()->getManager();
        $userEntreprise=$em->getRepository('AppBundle:AdminEntreprise')->findOneByUser($user)[0];
        $EntrepriseuUser=$userEntreprise->getEntreprise();
        
        $EntrepriseAgence=$agence->getEntreprise();
        if($EntrepriseuUser!=$EntrepriseAgence){
            return $this->redirectToRoute('agence_index');
        }
        
        $guichet=new Guichet();
        $guichet->setAgence($agence);
        $form = $this->createForm('AppBundle\Form\GuichetType', $guichet);
        $form->handleRequest($request);
        
        //var_dump('$request');        die();
        
        if ($form->isSubmitted() && $form->isValid()) {
                //var_dump('yes');                die();
                $guichet->setAgence($agence);
                
                $userForm= new User();
                //Infos User From Form
                $email = $request->get('email');
                $userForm->setEmail($email);
                $userForm->setUsername($email);
                
                $telephone = $request->get('telephone');
                $userForm->setTelephone($telephone);

                $password=$userForm->genererPassword();
                $userForm->setPassword($password);

                $roles[]='ROLE_GUICHET';
                $userForm->setRoles($roles);

                $userForm->setEnabled(true);
                
                //Determination du nom de guichet
                $guichets = $em->getRepository('AppBundle:Guichet')->findGuichetsByAgence($agence);
                $gui001='guichet001';
                $gui002='guichet002';
                $gui003='guichet003';
                $guixxx='';
                
                
                if(!$guichets){
                    $userForm->setNom('guichet001'); 
                } else {
                    
                    if(count($guichets)>=3){
                        return $this->redirectToRoute('agence_index');
                    }
                    
                    if(!$this->isGuichet($guichets, $gui001)){
                        $guixxx= $gui001;
                    }elseif (!$this->isGuichet($guichets, $gui002)) {
                        $guixxx= $gui002;
                    } else {
                        $guixxx= $gui003; 
                    }
                    
                    //var_dump($this->isGuichet($guichets, $gui002));                    die();
                    $userForm->setNom($guixxx); 
                }
                $guichet->setUser($userForm);
                
                //Droit Guichet
                $droitauto = 'oui';
                $guichet->setDroitauto($droitauto);
                 
                $em = $this->getDoctrine()->getManager();
                $em->persist($guichet);
                $em->flush();
                
                //Envoi du mail de Paramètres d'accès
                //Creer le transport
                $transport =(new \Swift_SmtpTransport('pro2.mail.ovh.net' , 587, 'tls'))
                              ->setUsername('samacoffre@samacoffre.sn')
                              ->setPassword('doklosB12');
                //Creer le transporteur
                $mailer = new \Swift_Mailer($transport);
                // Create a message
                $messagemailer= (new \Swift_Message("samaCOFFRE - Paramètres d'Accès Guichet"))
                            ->setFrom('samacoffre@samacoffre.sn')
                            ->setTo($email)
                            ->setBody(
                $this->renderView(
                // templates/emails/registration.html.twig
                        'emails/CodeAccesGuichet.html.twig',
                        ['nom' => $guichet,
                        'entreprise' => $agence->getEntreprise()->getNom(),
                        'email' => $email,
                        'password' => $password, ]
                    ),
                    'text/html'
                );
                // Send the message
                $mailer->send($messagemailer);
                 

                return $this->redirectToRoute('agence_index');
            }
        
        return $this->render('guichet/new.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
            'agence' => $agence,
        ));
    }
    
    /**
     * Displays a form to edit an existing guichet entity.
     *
     * @Route("/{id}/edit", name="guichet_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Guichet $guichet)
    {
        //blocage
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
           return $this->redirectToRoute('homepage');
        }
        $user=$this->getUser();
        //On compare l'entreprise user createur a celui de l'agence
        //Pour eviter un admin entreprise d'acceder aux infos de l'agence d'une autre entreprise
        $em = $this->getDoctrine()->getManager();
        $userEntreprise=$em->getRepository('AppBundle:AdminEntreprise')->findOneByUser($user)[0];
        $EntrepriseuUser=$userEntreprise->getEntreprise();
        
        $EntrepriseAgence=$guichet->getAgence()->getEntreprise();
        if($EntrepriseuUser!=$EntrepriseAgence){
            return $this->redirectToRoute('agence_index');
        }
        
     
        $deleteForm = $this->createDeleteForm($guichet);
        $editForm = $this->createForm('AppBundle\Form\GuichetType', $guichet);
        $editForm->handleRequest($request);
        
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            //var_dump('ee$editForm');die();
            $email = $request->get('email');
            $guichet->getUser()->setEmail($email);
            
            $telephone = $request->get('telephone');
            $guichet->getUser()->setTelephone($telephone);
            
            $droitauto = $request->get('droitauto');
            $guichet->setDroitauto($droitauto);
            try {
                
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
                        return $this->render('guichet/edit.html.twig', array(
                            'user' => $user,
                            'errorused'=>2,
                            'guichet' => $guichet,
                            'edit_form' => $editForm->createView(),
                            'delete_form' => $deleteForm->createView(),
                        ));                    
                    }elseif (!$mail->check($email)) {
                        
                        return $this->render('guichet/edit.html.twig', array(
                            'user' => $user,
                            'errorused'=>3,
                            'guichet' => $guichet,
                            'edit_form' => $editForm->createView(),
                            'delete_form' => $deleteForm->createView(),
                        ));
                      
                    }
                    //Validite Phone
                    if(strlen($telephone)<5){
                        
                         return $this->render('guichet/edit.html.twig', array(
                            'user' => $user,
                            'errorused'=>4,
                            'guichet' => $guichet,
                            'edit_form' => $editForm->createView(),
                            'delete_form' => $deleteForm->createView(),
                        ));
                         
                    }
                    
                    $this->getDoctrine()->getManager()->flush();
                  
            } catch (\Doctrine\DBAL\DBALException $ex) {
                
                return $this->render('guichet/edit.html.twig', array(
                    'user' => $user,
                    'errorused'=>1,
                    'guichet' => $guichet,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
                ));
                
            }
           

            return $this->redirectToRoute('guichet_edit', array('id' => $guichet->getId()));
        }

        return $this->render('guichet/edit.html.twig', array(
            'user' => $user,
            'guichet' => $guichet,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a guichet entity.
     *
     * @Route("/delete/{id}", name="guichet_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Guichet $guichet)
    {
        $user=$this->getUser();
        //blocage
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
           return $this->redirectToRoute('homepage');
        }
        //On compare l'entreprise user createur a celui de l'agence
        //Pour eviter un admin entreprise d'acceder aux infos de l'agence d'une autre entreprise
        $em = $this->getDoctrine()->getManager();
        $userEntreprise=$em->getRepository('AppBundle:AdminEntreprise')->findOneByUser($user)[0];
        $EntrepriseuUser=$userEntreprise->getEntreprise();
        
        $EntrepriseAgence=$guichet->getAgence()->getEntreprise();
        if($EntrepriseuUser!=$EntrepriseAgence){
            return $this->redirectToRoute('agence_index');
        }
        
       
             try {
                    $em = $this->getDoctrine()->getManager();
                    $em->remove($guichet);
                    $em->flush();
                    return $this->redirectToRoute('agence_index');
              } catch (\Doctrine\DBAL\DBALException $ex) {
                    return $this->redirectToRoute('guichet_edit', array('id' => $guichet->getId()));
             }
            
       

       
        //return $this->redirectToRoute('guichet_index');
    }

    /**
     * Creates a form to delete a guichet entity.
     *
     * @param Guichet $guichet The guichet entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Guichet $guichet)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('guichet_delete', array('id' => $guichet->getId())))
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
    
    /**
     * @Route("/agence/entreprise", name="guichet_agence_entreprise")
     */
    public function guichetentrepriseAction()
    {
        //blocage
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_GUICHET')){
           return $this->redirectToRoute('homepage');
        }
        
        $html='';
        $user=$this->getUser();
        $em = $this->getDoctrine()->getManager();
        $userGuichetEntreprise=$em->getRepository('AppBundle:Guichet')->findOneByUser($user);
        if($userGuichetEntreprise){
            
          
            $html .= strtoupper($userGuichetEntreprise[0]->getAgence()->getEntreprise()->getNom());
            $html .='/';
            $html .= strtoupper($userGuichetEntreprise[0]->getAgence()->getNom());
               
                
               
          
           
            
            //var_dump(count($userGuichetEntreprise));            die();
        }

        return new Response($html);
    }
}
