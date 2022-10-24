<?php

namespace AppBundle\Controller;

use AppBundle\Entity\InfoEntreprise;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * Infoentreprise controller.
 *
 * @Route("infoentreprise")
 */
class InfoEntrepriseController extends Controller
{
    /**
     * Lists all infoEntreprise entities.
     *
     * @Route("/", name="infoentreprise_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
         //On evite l'execution de cette fonction pour user connecte
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
            return $this->redirectToRoute('homepage');          
        }
        
        $user=$this->getUser();
        $em = $this->getDoctrine()->getManager();
        //On recupere l'entreprise de user createur
        $userEntreprise=$em->getRepository('AppBundle:AdminEntreprise')->findOneByUser($user)[0];
        $entreprise=$userEntreprise->getEntreprise();
        
//        //Tester l'autorisation pour ce Service
//        $serviceAutorise=FALSE;
//        $testServiceAutorise = $em->getRepository('AppBundle:EntrepriseService')->findByEntrepriseServiceLabel($entreprise, 'Documents Personnels');
//        if ($testServiceAutorise){
//            $serviceAutorise=TRUE; 
//        }
        
        $infoEntreprise = new Infoentreprise();
        $form = $this->createForm('AppBundle\Form\InfoEntrepriseType', $infoEntreprise);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $brochureFile */
            $brochureFile = $form->get('brochure')->getData();
            
//            if (!$serviceAutorise){
//                return $this->redirectToRoute('infoentreprise_index'); 
//            }
            
            if ($brochureFile) {
                
                $dateMS=date('dmY');
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);  
                //Nom Entreprise
                $nom=$entreprise->getNom();
                $nom= str_replace(' ', '', $nom);
                $nom = strtolower($nom);
                $dateEssai=$entreprise->getEssai();
                
                $newFilename = $nom.'-'.substr($dateMS, 2).'-'.uniqid().'.'.$brochureFile->guessExtension();   
                
                //$newFilename = $safeFilename.'-'.uniqid().'.'.$brochureFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $brochureFile->move(
                        $this->getParameter('documents_directory'),
                        $newFilename
                    );
                    
                    //Determination Nombre de Plis
                    $file= $this->getParameter('documents_directory').$newFilename;
                    $nbrPages=$this->getPDFPages($file);
                    $modulo=$nbrPages%4;
                    $plis=0;
                    if($modulo!=0){
                        $plis=   ($nbrPages-$modulo)/4 + 1;
                    } else {
                        $plis=   $nbrPages/4;
                    }
                     $infoEntreprise->setVolume($plis);
                     
                     //Page
                     $page=$nbrPages;
                     $infoEntreprise->setPage($nbrPages);
                     
                   
                    
                    //var_dump($plis);                    die();
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                 //var_dump($brochureFile);            die();
                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $infoEntreprise->setBrochureFilename($newFilename);
            }
            
           // $em = $this->getDoctrine()->getManager();
            $datedepot=new \DateTime('now');
            if($datedepot>$dateEssai){
                $infoEntreprise->setEssai('non');
            } else {
                $infoEntreprise->setEssai('oui');
            }
                    
            $infoEntreprise->setDate($datedepot);
            $infoEntreprise->setEntreprise($entreprise);
            
            $em->persist($infoEntreprise);
            $em->flush();
            
            //Cherche tous les Emplyes ou Clients Selon la Categorie
            $destinatires='';
            
            //var_dump($infoEntreprise->getId());            die();
            
            return $this->redirectToRoute('infoentreprise_index');
        }
        $infoEntreprises = $em->getRepository('AppBundle:InfoEntreprise')->findInformationsByEntreprise($entreprise);
        
        
        
        return $this->render('infoentreprise/index.html.twig', array(
            'infoEntreprises' => $infoEntreprises,
            'user' => $user,
            'infoEntreprise' => $infoEntreprise,
            'form' => $form->createView(),
//            'serviceAutorise' => $serviceAutorise,
        ));
    }

    

    /**
     * Finds and displays a infoEntreprise entity.
     *
     * @Route("/{id}", name="infoentreprise_show")
     * @Method("GET")
     */
    public function showAction(InfoEntreprise $infoEntreprise)
    {
         //On evite l'execution de cette fonction pour user connecte
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE') && !$this->get('security.authorization_checker')->isGranted('ROLE_COFFRE')){
            return $this->redirectToRoute('homepage');          
        }
        
        
        $user=$this->getUser();
        $em = $this->getDoctrine()->getManager();
        
        if($this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
                //On recupere l'entreprise de user createur
            $userEntreprise=$em->getRepository('AppBundle:AdminEntreprise')->findOneByUser($user)[0];
            $entrepriseUser=$userEntreprise->getEntreprise();
            //EntrepriseRequest
            $entrepriseRequest=$infoEntreprise->getEntreprise();
            if($entrepriseUser!=$entrepriseRequest){
              return $this->redirectToRoute('infoentreprise_index');  
            }
        }else{
            //ESt Service Pour User Pour Entreprise
            $entreprise=$infoEntreprise->getEntreprise();
            $testAccess = $em->getRepository('AppBundle:AccesReferenceService')->findByUserServiceEntreprise($user, 'Courriers Personnel Entreprise', $entreprise);
            if(!$testAccess){
                 return $this->redirectToRoute('homepage');   
            }
        }
        
         
         
        return $this->render('infoentreprise/show.html.twig', array(
            'infoEntreprise' => $infoEntreprise,
            'user' => $user,
        ));
    }

    /**
     * Displays a form to edit an existing infoEntreprise entity.
     *
     * @Route("/{id}/edit", name="infoentreprise_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, InfoEntreprise $infoEntreprise)
    {
         //On evite l'execution de cette fonction pour user connecte
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
            return $this->redirectToRoute('homepage');          
        }
        
        $user=$this->getUser();
        $em = $this->getDoctrine()->getManager();
        
        //On recupere l'entreprise de user createur
        $userEntreprise=$em->getRepository('AppBundle:AdminEntreprise')->findOneByUser($user)[0];
        $entrepriseUser=$userEntreprise->getEntreprise();
        //EntrepriseRequest
        $entrepriseRequest=$infoEntreprise->getEntreprise();
        if($entrepriseUser!=$entrepriseRequest){
          return $this->redirectToRoute('infoentreprise_index');  
        }
        
        $nature=$request->get('nature');
        $infoEntreprise->setNature($nature);
        
        $objet=$request->get('objet');
        if(trim($objet)!=''){
            $infoEntreprise->setObjet($objet); 
        }
       
       
            
        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute('infoentreprise_index');

    }

    /**
     * Deletes a infoEntreprise entity.
     *
     * @Route("/delete/{id}", name="infoentreprise_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, InfoEntreprise $infoEntreprise)
    {
         //On evite l'execution de cette fonction pour user connecte
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
            return $this->redirectToRoute('homepage');          
        }
        
        $user=$this->getUser();
        $em = $this->getDoctrine()->getManager();
        
        //On recupere l'entreprise de user createur
        $userEntreprise=$em->getRepository('AppBundle:AdminEntreprise')->findOneByUser($user)[0];
        $entrepriseUser=$userEntreprise->getEntreprise();
        //EntrepriseRequest
        $entrepriseRequest=$infoEntreprise->getEntreprise();
        if($entrepriseUser!=$entrepriseRequest){
          return $this->redirectToRoute('infoentreprise_index');  
        }
        
        $repUpload=$this->getParameter('documents_directory');
        unlink($repUpload.$infoEntreprise->getBrochureFilename());
        
        $em->remove($infoEntreprise);
        $em->flush();
        return $this->redirectToRoute('infoentreprise_index');
    }

    /**
     * Creates a form to delete a infoEntreprise entity.
     *
     * @param InfoEntreprise $infoEntreprise The infoEntreprise entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(InfoEntreprise $infoEntreprise)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('infoentreprise_delete', array('id' => $infoEntreprise->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
 

// Make a function for convenience 
function getPDFPages($document)
{   //Remote /usr/bin/pdfinfo  /local
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
