<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Certificat;
use AppBundle\Entity\CertificatService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Entreprise;
use Symfony\Component\HttpFoundation\JsonResponse;
/**
 * Certificat controller.
 *
 * @Route("certificat")
 */
class CertificatController extends Controller
{
    /**
     * Lists all periodes certificat entities.
     *
     * @Route("/", name="certificat_index")
     * @Method("GET")
     */
    public function indexAction()
    {
         //Contole profil
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
           return $this->redirectToRoute('homepage');
        }
        $em = $this->getDoctrine()->getManager();
        $user=$this->getUser();

        //Supposition de depots des mois precedents
        $annee=date('Y');
        $MoisDepot=date('Y-m');
        $moisanneEncours=date('m');
        //Sinon  des mois de l'annee dernieres
        if($moisanneEncours=='01'){
           $annee=$annee-1;
        }
       
        $isbisAnnee=$this->bissextile($annee);
        
        //var_dump($isbisAnnee);die();
        
        //I. Y'a t-il  Depots de Documents Personnalises
        $periodeDepots = $em->getRepository('AppBundle:CourrierEntreprise')->findByMsAnneeEssai($annee.'%');
        for($i=0;$i< count($periodeDepots);$i++){
            //Recuperation Mois
            $periode = $periodeDepots[$i]['periode'];
            $mois= substr($periodeDepots[$i]['periode'], 5);
            if($mois=='01' || $mois=='03' || $mois=='05' || $mois=='07' || $mois=='08' || $mois=='10' || $mois=='12'){
             $datecert=$annee.'-'.$mois.'-31';
            } else if ($mois=='02') {
                if($isbisAnnee){
                    $datecert=$annee.'-'.$mois.'-29';
                } else {
                    $datecert=$annee.'-'.$mois.'-28';
                }
            } else {
                $datecert=$annee.'-'.$mois.'-30'; 
            }
            
            //Entreprises deposants cette periode
            $Etrdeposants = $em->getRepository('AppBundle:CourrierEntreprise')->findByPeriodeEntreprisesEssai($periode.'%');
            //var_dump(count($Etrdeposants));die();
            for($j=0;$j< count($Etrdeposants);$j++){
                $entreprise = $em->getRepository('AppBundle:Entreprise')->findOneById($Etrdeposants[$j]['id']);
                $entreprise=$entreprise[0];
                
                 //Codage pour numerotation des factures
                $codeentreprise=$entreprise->getId();
                if(strlen($codeentreprise)==1){
                    $codeentreprise= '000'.$codeentreprise;
                }elseif(strlen($codeentreprise)==2){
                    $codeentreprise= '00'.$codeentreprise;
                }elseif(strlen($codeentreprise)==3){
                    $codeentreprise= '0'.$codeentreprise;
                }
                
                $numecert=$mois.$annee.$codeentreprise;
                
                $isCertificat = $em->getRepository('AppBundle:Certificat')->findByEntreprisePeriode($entreprise, $datecert);
                
                if(!$isCertificat){
                    $certificat = new Certificat();
                    $certificat->setEntreprise($entreprise);
                    $certificat->setDate(new \DateTime($datecert));
                    $certificat->setNumero($numecert);
                    $certificat->setPaiement('Pas encore');
                    $em->persist($certificat);
                    $em->flush();
                }

            }
        }
        
        //II. Y'a t-il  Depots Pour Communications 
        $periodeDepots = $em->getRepository('AppBundle:InfoEntreprise')->findByMsAnneeEssai($annee.'%');
        for($i=0;$i< count($periodeDepots);$i++){
            //Recuperation Mois
            $periode = $periodeDepots[$i]['periode'];
            $mois= substr($periodeDepots[$i]['periode'], 5);
            if($mois=='01' || $mois=='03' || $mois=='05' || $mois=='07' || $mois=='08' || $mois=='10' || $mois=='12'){
             $datecert=$annee.'-'.$mois.'-31';
            } else if ($mois=='02') {
                if($isbisAnnee){
                    $datecert=$annee.'-'.$mois.'-29';
                } else {
                    $datecert=$annee.'-'.$mois.'-28';
                }
            } else {
                $datecert=$annee.'-'.$mois.'-30'; 
            }
            
            //Entreprises deposants cette periode
            $Etrdeposants = $em->getRepository('AppBundle:InfoEntreprise')->findByPeriodeEntreprisesEssai($periode.'%');
            for($j=0;$j< count($Etrdeposants);$j++){
                $entreprise = $em->getRepository('AppBundle:Entreprise')->findOneById($Etrdeposants[$j]['id']);
                $entreprise=$entreprise[0];
                
                //Codage pour numerotation des factures
                $codeentreprise=$entreprise->getId();
                if(strlen($codeentreprise)==1){
                    $codeentreprise= '000'.$codeentreprise;
                }elseif(strlen($codeentreprise)==2){
                    $codeentreprise= '00'.$codeentreprise;
                }elseif(strlen($codeentreprise)==3){
                    $codeentreprise= '0'.$codeentreprise;
                }
                
                $numecert=$mois.$annee.$codeentreprise;
                
                $isCertificat = $em->getRepository('AppBundle:Certificat')->findByEntreprisePeriode($entreprise, $datecert);
                if(!$isCertificat){
                    $certificat = new Certificat();
                    $certificat->setEntreprise($entreprise);
                    $certificat->setDate(new \DateTime($datecert));
                    $certificat->setNumero($numecert);
                    $certificat->setPaiement('Pas encore');
                    $em->persist($certificat);
                    $em->flush();
                }

            }
        }


        //III. Creation Lignes certifcats Services
        $certificats = $em->getRepository('AppBundle:Certificat')->findByPeriode($annee.'%');
        for($i=0;$i< count($certificats);$i++){
            $entreprise=$certificats[$i]->getEntreprise();
            $periodeMS= substr($certificats[$i]->getDate()->format('Y-m-d'), 0, 7);
            
            //Courriers Personnalises
            $services = $em->getRepository('AppBundle:CourrierEntreprise')->findByEntreprisePeriodeEssai($entreprise, $periodeMS.'%');
            for($j=0;$j< count($services);$j++){
                $service = $services[$j]['id'];
                $service=$em->getRepository('AppBundle:EntrepriseService')->findOneById($service)[0];

                $prixunitaire =$service->getService()->getPrixunitaire();
                if($service->getDroitinout()!='Client'){
                    $prixunitaire=$prixunitaire + $service->getService()->getCouttraitement();
                }
                if($service->getStockage()!='3 mois'){
                    $prixunitaire=$prixunitaire + $service->getService()->getCoutstockage();
                }
                $nbplis = $services[$j]['somme'];
                
                $isCertificatService=$em->getRepository('AppBundle:CertificatService')->findByCertificatServiceCategorie($certificats[$i], $service->getService()->getService());
                if(!$isCertificatService){
                    $certificatservice = new CertificatService();
                //Initialisation
                    $certificatservice->setCertificat($certificats[$i]);
                    //$certificatservice->setEntrepriseservice($service);
                     $certificatservice->setCategorie($service->getService()->getService());
                    $certificatservice->setPrixunitaire($prixunitaire);
                    $certificatservice->setNbplis($nbplis);
                    
                    $em->persist($certificatservice);
                    $em->flush();
                }
                
                
               
            }
            
            //Communications
            $categorie="Communications";
            $infosEntreprise = $em->getRepository('AppBundle:InfoEntreprise')->findByEntreprisePeriodeEssai($entreprise, $periodeMS.'%');
            
            if($infosEntreprise){
//                $service= $em->getRepository('AppBundle:EntrepriseService')->findByEntrepriseServiceLabel($entreprise, 'Documents Employé');
//                $service=$service[0];
                $service=$em->getRepository('AppBundle:Tarif')->findByService('Documents Salarié');
                $service=$service[0];
                $prixunitaire =$service->getPrixunitaire();
                
                $nbplis = $infosEntreprise[0]['somme'];
                
               

                $isCertificatServiceCategorie=$em->getRepository('AppBundle:CertificatService')->findByCertificatServiceCategorie($certificats[$i], 'Communications');
                if(!$isCertificatServiceCategorie){
                    $certificatservice = new CertificatService();
                    //Initialisation
                    $certificatservice->setCertificat($certificats[$i]);
//                    $certificatservice->setEntrepriseservice(NULL);
                    $certificatservice->setCategorie($categorie);
                    $certificatservice->setPrixunitaire($prixunitaire);
                    $certificatservice->setNbplis($nbplis);

                    $em->persist($certificatservice);
                    $em->flush();
                } 
            }
            
        }
        
         
        //IV. Determinations  Montants Certificats
        $certificats = $em->getRepository('AppBundle:Certificat')->findByPeriode($annee.'%');
        $montantCert=0;
        for($i=0;$i< count($certificats);$i++){
            //On cherche les lignes certicats
            $montantCert=0;
            $certificat=$certificats[$i];
            $lignesCertis=$em->getRepository('AppBundle:CertificatService')->findByLigneServiceCertificat($certificat);
            
            for($j=0;$j< count($lignesCertis);$j++){
                $ligne=$lignesCertis[$j];
                $prixunitaire=$ligne->getPrixunitaire();
                $nbplis=$ligne->getNbplis();
                $montantCert=$montantCert+(int)$prixunitaire*(int)$nbplis;
            }
            
            //$montantCert=$montantCert+$montantCert*18/100; 
             $montantHT =round($montantCert/1.18);
             
             $tva=$montantCert-$montantHT;
             
             //var_dump($montantHT);             die();
               //var_dump($tva);             die();
               
            $certificat->setMontant($montantCert);
             $certificat->setHorstva($montantHT);
            $certificat->setTva($tva);
            
            $em->persist($certificat);
            $em->flush();
        }
         
        //Besion Affichage Par Mois
        $certificats = $em->getRepository('AppBundle:Certificat')->findByMsAnnee($annee.'%');
        return $this->render('certificat/index.html.twig', array(
            'certificats' => $certificats,
            'user' => $user,
            'annee' => $annee,
        ));
    }

    /**
     * Lists all certificat entities.
     *
     * @Route("/certificats", name="certificat_certificat")
     * @Method("GET")
     */
    public function certificatAction(Request $request)
    {
         //Contole profil
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
           return $this->redirectToRoute('homepage');
        }
        
        $em = $this->getDoctrine()->getManager();
        $user=$this->getUser();
        $annee=date('Y');
        $periode = $request->get('periode');
        //var_dump('$em');die();
         $certificats = $em->getRepository('AppBundle:Certificat')->findByPeriode($periode.'%');

        if(!$certificats){
            return $this->redirectToRoute('certificat_index');
        }
       
        return $this->render('certificat/certificats.html.twig', array(
            'certificats' => $certificats,
            'user' => $user,
            'periode' => $periode,
        ));
    }
    
    
    
    /**
     * Lists all periodes certificat entities.
     *
     * @Route("/client/{id}", name="certificat_client")
     * @Method("GET")
     */
    public function clientAction(Entreprise $entreprise)
    {       
         //Seuls les Clients Entreprise et l'Admin sont autorises
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') && !$this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
           return $this->redirectToRoute('homepage');
        }
         
        $em = $this->getDoctrine()->getManager();
        $user=$this->getUser();
        
        
        //Supposition de depots des mois precedents de l'annee en cours
        $annee=date('Y');
        $MoisDepot=date('Y-m');
        $moisanneEncours=date('m');
        //Sinon  des mois de l'annee dernieres
        if($moisanneEncours=='01'){
           $annee=$annee-1;
        }
       
        //Si l'utilisateur est un Client Entreprise, on si l'Entreprise en question est la sienne
        if($this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
            $userEntreprise=$em->getRepository('AppBundle:AdminEntreprise')->findOneByUser($user)[0];
            $EntrepriseuUser=$userEntreprise->getEntreprise();
            
            if($EntrepriseuUser!=$entreprise){
               return $this->redirectToRoute('entrepriseservice_entreprise');
            }
        }
        
        //On verifie si l'annee est bisextile pour determiner le mois de fevrier 
        $isbisAnnee=$this->bissextile($annee);
        
        //Codage pour numerotation des factures
        $codeentreprise=$entreprise->getId();
        if(strlen($codeentreprise)==1){
            $codeentreprise= '000'.$codeentreprise;
        }elseif(strlen($codeentreprise)==2){
            $codeentreprise= '00'.$codeentreprise;
        }elseif(strlen($codeentreprise)==3){
            $codeentreprise= '0'.$codeentreprise;
        }
        
        //I. Y'a t-il  Depots de Documents Personnalises   findByMsAnneeEntrepriseEssai
        $periodeDepots = $em->getRepository('AppBundle:CourrierEntreprise')->findByMsAnneeEntrepriseEssai($annee.'%', $entreprise);
           
        
        /* 1. Determination numero facture
         * 2. Determination date facture
         * 3. Initailisation l'entite facture/certificat
         */
        for($i=0;$i< count($periodeDepots);$i++){
            //Recuperation Mois
            $periode = $periodeDepots[$i]['periode'];
           
            if($periode!=$MoisDepot){
                $mois= substr($periodeDepots[$i]['periode'], 5);
                if($mois=='01' || $mois=='03' || $mois=='05' || $mois=='07' || $mois=='08' || $mois=='10' || $mois=='12'){
                 $datecert=$annee.'-'.$mois.'-31';
                 $numecert=$mois.$annee.$codeentreprise;
                } else if ($mois=='02') {
                    $numecert=$mois.$annee.$codeentreprise;
                    if($isbisAnnee){
                        $datecert=$annee.'-'.$mois.'-29';
                    } else {
                        $datecert=$annee.'-'.$mois.'-28';
                    }
                } else {
                    $numecert=$mois.$annee.$codeentreprise;
                    $datecert=$annee.'-'.$mois.'-30'; 
                }

                
                
                //AnttttDetermination Anterieur facture
                if($mois!='01'){
                    $manter=$mois-1;
                    if($manter<10){
                      $manter=$annee.'-0'.$manter;  
                    }
                } else {
                    $anpasse=$annee-1;
                    $manter=$anpasse.'-12';   
                    
                    //var_dump('$manter');                    die();
                }
//                $anterieur = $em->getRepository('AppBundle:Certificat')->findByEntreprisePeriodeAnterieur($entreprise, $manter.'%');
               //AnttttDetermination Anterieur facture
                

                //Init Entity Certificat Par Periode
                $isCertificat = $em->getRepository('AppBundle:Certificat')->findByEntreprisePeriode($entreprise, $datecert);   
                if(!$isCertificat){
                    $certificat = new Certificat();
                    $certificat->setEntreprise($entreprise);
                    $certificat->setDate(new \DateTime($datecert));
                    $certificat->setNumero($numecert);
                    $certificat->setPaiement('Non Payée');
//                    if($anterieur){
//                        $certificat->setAnterieur($anterieur[0]);
//                    }
                    $em->persist($certificat);
                    $em->flush();
                } 
//                else {
//                    $certificat=$isCertificat[0];
//                    $certificat->setNumero($numecert);
//                     $certificat->setPaiement('Non Payée');
//                     if($anterieur){
//                         $certificat->setAnterieur($anterieur[0]);
//                    }
//                    $em->persist($certificat);
//                    $em->flush();
//                } 
            }
 
        }
        
        
        //II. Y'a t-il  Depots Pour Communications        findByMsAnneeEntrepriseEssai
        $periodeDepots = $em->getRepository('AppBundle:InfoEntreprise')->findByMsAnneeEntrepriseEssai($annee.'%', $entreprise);
        for($i=0;$i< count($periodeDepots);$i++){
            //Recuperation Mois
            $periode = $periodeDepots[$i]['periode'];
            if($periode!=$MoisDepot){
                $mois= substr($periodeDepots[$i]['periode'], 5);
                if($mois=='01' || $mois=='03' || $mois=='05' || $mois=='07' || $mois=='08' || $mois=='10' || $mois=='12'){
                 $datecert=$annee.'-'.$mois.'-31';
                  $numecert=$mois.$annee.$codeentreprise;
                } else if ($mois=='02') {
                     $numecert=$mois.$annee.$codeentreprise;
                    if($isbisAnnee){
                        $datecert=$annee.'-'.$mois.'-29';
                    } else {
                        $datecert=$annee.'-'.$mois.'-28';
                    }
                } else {
                    $datecert=$annee.'-'.$mois.'-30'; 
                     $numecert=$mois.$annee.$codeentreprise;
                }
                
                //Determination Anterieur facture
                if($mois!='01'){
                    $manter=$mois-1;
                    if($manter<10){
                      $manter=$annee.'-0'.$manter;  
                    }
                } else {
                    $anpasse=$annee-1;
                    $manter=$anpasse.'-12';    
                }
//                $anterieur = $em->getRepository('AppBundle:Certificat')->findByEntreprisePeriodeAnterieur($entreprise, $manter.'%');
                //Determination Anterieur facture

                //Init Entity Certificat Par Periode
                $isCertificat = $em->getRepository('AppBundle:Certificat')->findByEntreprisePeriode($entreprise, $datecert);   
                if(!$isCertificat){
                    $certificat = new Certificat();
                    $certificat->setEntreprise($entreprise);
                    $certificat->setDate(new \DateTime($datecert));
                    $certificat->setNumero($numecert);
                    $certificat->setPaiement('Non Payée');
//                    if($anterieur){
//                         $certificat->setAnterieur($anterieur[0]);
//                    }
                    $em->persist($certificat);
                    $em->flush();
                }
//                else {
//                    $certificat=$isCertificat[0];
//                    $certificat->setNumero($numecert);
//                     $certificat->setPaiement('Non Payée');
//                     if($anterieur){
//                         $certificat->setAnterieur($anterieur[0]);
//                    }
//                    $em->persist($certificat);
//                    $em->flush();
//                } 
            } 
            
        }
        
         
        
        //III. Creation Lignes certifcats Services
        $certificats = $em->getRepository('AppBundle:Certificat')->findByPeriodeEntreprise($annee.'%', $entreprise);
        for($i=0;$i< count($certificats);$i++){
            $entreprise=$certificats[$i]->getEntreprise();
            $periodeMS= substr($certificats[$i]->getDate()->format('Y-m-d'), 0, 7);
            $services = $em->getRepository('AppBundle:CourrierEntreprise')->findByEntreprisePeriodeEssai($entreprise, $periodeMS.'%');
            //$periodeMS=$periodeMS['date'];
            
            for($j=0;$j< count($services);$j++){
                
                $service = $services[$j]['id'];
                $service=$em->getRepository('AppBundle:EntrepriseService')->findOneById($service)[0];

                $prixunitaire =$service->getService()->getPrixunitaire();
//                if($service->getDroitinout()!='Client'){
//                    $prixunitaire=$prixunitaire + $service->getService()->getCouttraitement();
//                }
                if($service->getStockage()!='3 mois'){
                    $prixunitaire=$prixunitaire + $service->getService()->getCoutstockage();
                }
               
                
                $nbplis = $services[$j]['somme'];
               
                
              
                
//                 var_dump($prixunitaire);                die();
                
                $isCertificatService=$em->getRepository('AppBundle:CertificatService')->findByCertificatServiceCategorie($certificats[$i], $service->getService()->getService());
                if(!$isCertificatService){
                    $certificatservice = new CertificatService();
                //Initialisation
                    $certificatservice->setCertificat($certificats[$i]);
                    
                    //$certificatservice->setEntrepriseservice($service);
                    
                    $certificatservice->setCategorie($service->getService()->getService());
                    $certificatservice->setPrixunitaire($prixunitaire);
                    $certificatservice->setNbplis($nbplis);
                    
                    $em->persist($certificatservice);
                    $em->flush();
                    
                }
               
            }
            //Pour Chaque Certificat Ya til Comm Entreprise
            //Si Oui,  On Teste l'Enregistre de Son Certificatservice
            $categorie="Communications";
            $infosEntreprise = $em->getRepository('AppBundle:InfoEntreprise')->findByEntreprisePeriodeEssai($entreprise, $periodeMS.'%');
            
            if($infosEntreprise){
//                $service= $em->getRepository('AppBundle:EntrepriseService')->findByEntrepriseServiceLabel($entreprise, 'Documents Employé');
//                $service=$service[0];
                //Pour determination prix comm
                $service=$em->getRepository('AppBundle:Tarif')->findByService('Documents Salarié');
                $service=$service[0];
                $prixunitaire =$service->getPrixunitaire();
                
                $nbplis = $infosEntreprise[0]['somme'];
                
               

                $isCertificatServiceCategorie=$em->getRepository('AppBundle:CertificatService')->findByCertificatServiceCategorie($certificats[$i], $categorie);
                if(!$isCertificatServiceCategorie){
                    $certificatservice = new CertificatService();
                    //Initialisation
                    $certificatservice->setCertificat($certificats[$i]);
//                    $certificatservice->setEntrepriseservice(NULL);
                    $certificatservice->setCategorie($categorie);
                    $certificatservice->setPrixunitaire($prixunitaire);
                    $certificatservice->setNbplis($nbplis);

                    $em->persist($certificatservice);
                    $em->flush();
                } 
            }
            
        }
        
        //On Dertermine le Montant des Certificar
        $montantCert=0;
        for($i=0;$i< count($certificats);$i++){
            //On cherche les lignes certicats
            $montantCert=0;
            $certificat=$certificats[$i];
            $lignesCertis=$em->getRepository('AppBundle:CertificatService')->findByLigneServiceCertificat($certificat);
            
            for($j=0;$j< count($lignesCertis);$j++){
                $ligne=$lignesCertis[$j];
                $prixunitaire=$ligne->getPrixunitaire();
                $nbplis=$ligne->getNbplis();
                $montantCert=$montantCert+(int)$prixunitaire*(int)$nbplis;
            }
            
            //$montantCert=$montantCert+$montantCert*18/100; 
             $montantHT =round($montantCert/1.18);
             
             $tva=$montantCert-$montantHT;
             
             //var_dump($montantHT);             die();
               //var_dump($tva);             die();
               
            $certificat->setMontant($montantCert);
             $certificat->setHorstva($montantHT);
            $certificat->setTva($tva);
            
            $em->persist($certificat);
            $em->flush();
        }
         
           
        //findByMsAnneeEntreprise($annee, $entreprise)
        //Listing
        $certificats = $em->getRepository('AppBundle:Certificat')->findByMsAnneeEntreprise($annee.'%', $entreprise);
        return $this->render('certificat/client.html.twig', array(
            'certificats' => $certificats,
            'entreprise' => $entreprise,
            'user' => $user,
            'annee' => $annee, 
        ));
    }

    /**
     * Finds and displays a certificat entity.
     *
     * @Route("/{id}", name="certificat_show")
     * @Method("GET")
     */
    public function showAction(Certificat $certificat, Request $request)
    {
         //Contole profil
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') && !$this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
           return $this->redirectToRoute('homepage');
        }
        
       
        
        $em = $this->getDoctrine()->getManager();
        $deleteForm = $this->createDeleteForm($certificat);
        $user=$this->getUser();
        $client = $request->get('client');
        
        if($this->get('security.authorization_checker')->isGranted('ROLE_ENTREPRISE')){
           if($client!='y'){
                return $this->redirectToRoute('entrepriseservice_entreprise');
           } else {
               $userEntreprise=$em->getRepository('AppBundle:AdminEntreprise')->findOneByUser($user)[0];
               $EntrepriseuUser=$userEntreprise->getEntreprise();
               $entreprise=$certificat->getEntreprise();
               if($EntrepriseuUser!=$entreprise){
                    return $this->redirectToRoute('entrepriseservice_entreprise');
                }
           }
        }

        //var_dump('$entreprise');die();
          $certificats=$em->getRepository('AppBundle:CertificatService')->findByCertificatFacture($certificat); 
          
          
           $formatter = \NumberFormatter::create('fr_FR', \NumberFormatter::SPELLOUT);
            $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, 0);
            $formatter->setAttribute(\NumberFormatter::ROUNDING_MODE, \NumberFormatter::ROUND_HALFUP);
            $ttlettre= $formatter->format($certificat->getMontant());   // un million cinq cent vingt-deux mille cinq cent trente
        
        return $this->render('certificat/show.html.twig', array(
            'certificats' => $certificats,
            'certificat' => $certificat,
            'delete_form' => $deleteForm->createView(),
            'user' => $user,
             'client' => $client,
             'ttlettre' => $ttlettre,
        ));
    }
    
    /**
     * Finds and displays a certificat entity.
     *
     * @Route("/all", name="certificat_all")
     * @Method("GET")
     */
    public function allShowAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user=$this->getUser();
        $client = $request->get('client');
        
        //Par Periode findByPeriode($periode);
        
        
        //Par Entreprise Periode findByEntreprisePeriode($entreprise, $periode)

        return $this->render('certificat/allShow.html.twig', array(
            'certificats' => $certificats,
            
            'user' => $user,
            'client' => $client,
        ));
    }
    
     /**
     * Finds and displays a certificat entity.
     *
     * @Route("/reglements/", name="certificat_reglements")
     * @Method("GET")
     */
    public function reglementsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user=$this->getUser();
//        var_dump('$user');die();

        return $this->render('certificat/reglements.html.twig', array(
//            'certificats' => $certificats,
            'user' => $user,
        ));
    }
    
     /**
     * Finds and displays a certificat entity.
     *
     * @Route("/search/facture/", name="certificat_searchfacture")
     * @Method("GET")
     */
    public function searchFactueAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user=$this->getUser();
        
        //Infos Facture
        $numero=$request->get('numero');
        $Certificat=$em->getRepository('AppBundle:Certificat')->findByNumero($numero);
        
        $id='';
        $numero='';
        $entreprise='';
        $montant='';
        $horstva='';
        $tva='';
        $paiement='';
        $datepaie='';

        if($Certificat){
            $Certificat=$Certificat[0];
            $id=$Certificat->getId();
            $numero=$Certificat->getNumero();
            $entreprise=$Certificat->getEntreprise()->getNom();
            $montant=$Certificat->getMontant();
            $horstva=$Certificat->getHorstva();
            $tva=$Certificat->getTva();
            $paiement=$Certificat->getPaiement();
            $datepaie=$Certificat->getDatepaie();
            
        }
        
        var_dump($entreprise);die();

        return $this->render('certificat/reglements.html.twig', array(
//            'certificats' => $certificats,
            'user' => $user,
        ));
    }
    
    /**
     * @Route("/pdf/{id}", name="certificat_pdfShow")
     * @Method("GET")
     */
    public function pdfShowAction(Certificat $certificat)
    { 
            $em = $this->getDoctrine()->getManager();
            
            //Recuperation Plis Information Entreprise du Mois
            $entreprise=$certificat->getEntreprise();
            $moisannee=$certificat->getDate()->format('Y-m');
            
            //var_dump($moisannee);die();
            
            $pdf = new \FPDF();
            $pdf->AddPage();
            
            //Header page
            $pdf->Image("img/logo-samacoffre.png" ,3 ,3 ,30 ,20);
            $pdf->Ln(5);
            //Body page
            $pdf->SetFont('Helvetica','', 11);
            $pdf->Cell(120,6,'',0,0,'C');
            
            //Date
            $annee=$certificat->getDate()->format('Y');
            $mmois=$certificat->getDate()->format('m');
            //var_dump($mmois);die();
            $fact=$mmois;
            if($mmois==1){
                $mmois='Janvier';  
            }elseif($mmois==2) {
                $mmois='Février';  
            }elseif($mmois==3) {
                $mmois='Mars';
            }elseif($mmois==4) {
                $mmois='Avril';
            }elseif($mmois==5) {
                $mmois='Mai';
            }elseif($mmois==6) {
                $mmois='Juin';
            }elseif($mmois==7) {
                $mmois='Juillet';
            }elseif ($mmois==8){
                $mmois='Août';
            }elseif ($mmois==9){
                $mmois='Septembre';
            }elseif ($mmois==10){
                $mmois='Octobre';
            }elseif ($mmois==11){
                $mmois='Novembre';
            }else {
                $mmois='Décembre';
            }
            $jjour=$certificat->getDate()->format('d');
            $pdf->SetFont('Helvetica','', 11);
            $pdf->Cell(76,6,utf8_decode('Le '.$jjour.' '.$mmois.' '.$annee),0,1,'R');
            $pdf->Ln(6);
            
            //Facture
            $pdf->SetFont('Helvetica','B', 11);
            $pdf->Cell(98,6,utf8_decode('Facture N°'),0,0,'R');
            
            $idetr=$certificat->getEntreprise()->getId();
            if(strlen($idetr)==1){
                $idetr= '000'.$idetr;
            }elseif(strlen($idetr)==2){
                $idetr= '00'.$idetr;
            }elseif(strlen($idetr)==3){
                $idetr= '0'.$idetr;
            }
            
            $numFacture=$certificat->getNumero();
            $pdf->SetFont('Helvetica','', 11);
            $pdf->Cell(98,6,$numFacture,0,1,'L');
            
            $pdf->Ln(2);
             
            //Client
            $pdf->SetFont('Helvetica','B', 11);
            $str= utf8_encode($certificat->getEntreprise()->getNom());
            $pdf->Cell(103,12,strtoupper($str),0,0,'L');
            
            //Periode
            $pdf->SetFont('Helvetica','B', 11);
            $pdf->Cell(90,6, utf8_decode('Dépôts Dans Coffres'),1,1,'C');
              
            $pdf->Cell(103,1, '',0,0,'L');
            $pdf->Cell(45,1, '','LR',0,'L');
            $pdf->Cell(45,1, '','R',1,'R');
            
            
              
            
            
            $pdf->SetFont('Helvetica','', 11);
            $adresse= utf8_encode($certificat->getEntreprise()->getAdresse());
            $pdf->Cell(103,6,$adresse,0,0,'L');
            $pdf->SetFont('Helvetica','', 11);
            $pdf->Cell(45,6,'Du 01'.'/'.$fact.'/'.$annee,'LRB',0,'L');
            $pdf->Cell(45,6,'Au '.$jjour.'/'.$fact.'/'.$annee,'LRB',1,'R');
            
            
            //Reference client
             $pdf->Cell(193,1, '',0,1,'L');
             
             $pdf->SetFont('Helvetica','B', 11);
             $pdf->Cell(35,1,utf8_decode(''),0,0,'L');
             $pdf->SetFont('Helvetica','', 11);
             $pdf->Cell(158,1, ' ' ,0,1,'L');
             
             
            $pdf->Ln(15);
            
            //Enetete
            $pdf->SetFont('Helvetica','B', 11);
            $pdf->Cell(98,6,'LIBELLE',1,0,'C');
            $pdf->Cell(30,6,'QUANTITE',1,0,'C');
            $pdf->Cell(25,6,'PRIX',1,0,'C');
            $pdf->Cell(40,6,'TOTAL',1,1,'C');
            
            //Espace Entete
            $pdf->SetFont('Helvetica','', 11);
            $pdf->Cell(98,2,'','LR',0,'C');
            $pdf->Cell(30,2,'','LR',0,'C');
            $pdf->Cell(25,2,'','LR',0,'C');
            $pdf->Cell(40,2,'','LR',1,'C');
            
            //Corps
            $brEnr=0;
            $ttotal=0;
            $certificats=$em->getRepository('AppBundle:CertificatService')->findByCertificatFacture($certificat);   
            for($i=0;$i< count($certificats);$i++){
               
                    $pdf->Cell(98,8, utf8_decode($certificats[$i]->getCategorie()),'LR',0,'C');
                    $pdf->Cell(30,8,$certificats[$i]->getNbplis(),'LR',0,'C');
                    $pdf->Cell(25,8,intval($certificats[$i]->getPrixunitaire()),'LR',0,'C');
                    $total=$certificats[$i]->getNbplis()*intval($certificats[$i]->getPrixunitaire());
                    $pdf->Cell(40,8,$this->inserEspace($total),'LR',1,'R');
                    $brEnr=$brEnr+1;
                    
                    $ttotal=$ttotal+$total;
            }
            
            //Complements
            for($i=$brEnr+1;$i<=10;$i++){
                $pdf->Cell(98,8,'','LR',0,'C');
                $pdf->Cell(30,8,'','LR',0,'C');
                $pdf->Cell(25,8,'','LR',0,'C');
                $pdf->Cell(40,8,'','LR',1,'C');
            }
            
            //Espace Fin Corps
            $pdf->SetFont('Helvetica','', 11);
            $pdf->Cell(98,2,'','LRB',0,'C');
            $pdf->Cell(30,2,'','LRB',0,'C');
            $pdf->Cell(25,2,'','LRB',0,'C');
            $pdf->Cell(40,2,'','LRB',1,'C');
            
           
            
            //TTotal
            $pdf->SetFont('Helvetica','B', 11);
            $pdf->Cell(98,8,utf8_decode(''),0,0,'C');
            $pdf->Cell(55,8,'Total HT','LRB',0,'C');
             $pdf->SetFont('Helvetica','', 11);
            $pdf->Cell(40,8, $this->inserEspace($certificat->getHorstva()),'LRB',1,'R');
            
            //HorsTaxe
            $pdf->SetFont('Helvetica','B', 11);
            $pdf->Cell(49,8, '',0,0,'C');
            $pdf->Cell(49,8,'',0,0,'C');
            $pdf->Cell(55,8,'TVA 18%','LRB',0,'C');
            $pdf->SetFont('Helvetica','', 11);
            $horstaxe=$ttotal*18/100;
            $pdf->Cell(40,8, $this->inserEspace($certificat->getTva()),'LRB',1,'R');
            
            //Cumul
            $numanterieure='';
            $montanterieure='';
//           if($certificat->getAnterieur()!=NULL){
//              $numanterieure = $certificat->getAnterieur()->getNumero();
//              $montanterieure= $certificat->getAnterieur()->getMontant();
//           }
            $pdf->Cell(49,8,$numanterieure,0,0,'C');
            $pdf->Cell(49,8,$this->inserEspace($montanterieure),0,0,'C');
            
             $pdf->SetFont('Helvetica','B', 11);
            $pdf->Cell(55,8,'Total TTC','LRB',0,'C');
             $pdf->SetFont('Helvetica','', 11);
            $cumul=$ttotal+$horstaxe;
            $pdf->Cell(40,8, $this->inserEspace($certificat->getMontant()),'LRB',1,'R');
            
            
            
            $formatter = \NumberFormatter::create('fr_FR', \NumberFormatter::SPELLOUT);
            $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, 0);
            $formatter->setAttribute(\NumberFormatter::ROUNDING_MODE, \NumberFormatter::ROUND_HALFUP);
            $ttlettre= $formatter->format($certificat->getMontant());   // un million cinq cent vingt-deux mille cinq cent trente

             $pdf->Ln(6);
            $pdf->SetFont('Helvetica','', 11);
            $pdf->Cell(93,8,utf8_decode('Arrétée la Présente Facture à la Somme de '),0,0,'L');
            $pdf->SetFont('Helvetica','B', 11);
            $pdf->Cell(100,8,$this->inserEspace($certificat->getMontant()).' FCFA',0,0,'L');
            $pdf->SetFont('Helvetica','', 11);
            $pdf->Ln(6);
            
            $pdf->SetFont('Helvetica','B', 11);
            $pdf->Cell(193,6,utf8_decode(strtoupper($ttlettre).' FCFA'),0,1,'C');
            
            $pdf->SetFont('Helvetica','UB', 11);
            $pdf->Cell(193,6,utf8_decode('MOYENS DE PAIEMENT :'),0,1,'L');
            $pdf->SetFont('Helvetica','', 11);
            
            
            $vrmfacture=' BIS N°251144287001, Code Banque: SN079, Code Guichet: 01118, Clé RIB: 33';
            $pdf->SetFont('Helvetica','B', 11);
            $pdf->Cell(33,6,utf8_decode('>> Par Virement : '),0,0,'L');
            $pdf->SetFont('Helvetica','', 11);
            $pdf->Cell(160,6,utf8_decode($vrmfacture),0,1,'L');
            
           
            $pdf->Cell(33,6,utf8_decode(''),0,0,'L');
            $pdf->SetFont('Helvetica','', 11);
            $pdf->Cell(160,6,'Compte (Administrateur) : Idrissa MANE',0,1,'L');
            
            $pdf->SetFont('Helvetica','B', 11);
            $pdf->Cell(13,6,'',0,0,'L');
            $pdf->Cell(180,6,utf8_decode('Attention :'),0,1,'L');
            $pdf->SetFont('Helvetica','', 11);
            $pdf->Cell(20,6,'',0,0,'L');
            $pdf->Cell(173,6,utf8_decode('1. Le paiement par virement bancaire entraîne systématiquement un traitement minimum de trois jours.'),0,1,'L');
            
            $pdf->SetFont('Helvetica','', 11);
            $pdf->Cell(20,6,'',0,0,'L');
            $pdf->Cell(83,6,utf8_decode("2. N'oubliez pas d'indiquer le numéro de facture "),0,0,'L');
             $pdf->SetFont('Helvetica','B', 11);
            $pdf->Cell(21,6,utf8_decode($numFacture),0,0,'L');
             $pdf->SetFont('Helvetica','', 11);
            $pdf->Cell(69,6,utf8_decode(" dans le champ Communication de l'opéra-"),0,1,'L');

            $pdf->SetFont('Helvetica','', 11);
            $pdf->Cell(22,6,'',0,0,'L');
            $pdf->Cell(171,6,utf8_decode("  tion bancaire sans lequel  votre paiement ne pourra être validé."),0,1,'L');

            $pdf->Ln(6);
            $pdf->SetFont('Helvetica','B', 11);
            $pdf->Cell(33,6,utf8_decode('>> Electronique : '),0,0,'L');
            $pdf->SetFont('Helvetica','', 11);
            $pdf->Cell(160,6,utf8_decode('Dans Bientôt !'),0,1,'L');
            
            

            
            //Signature
           $pdf->Ln(18);
//           $pdf->Cell(97,8,'LE DIRECTEUR DES FINANCES',0,0,'C');
//           $pdf->Cell(96,8,'LE DIRECTEUR GENERAL',0,1,'C');
//            $pdf->Ln(-3);
//           $pdf->Cell(97,8,'ET DE LA COMPTABILITE',0,0,'C');
//           $pdf->Cell(96,8,'',0,1,'C');
           
           
           //Taille Unitaire Facturation: 480 KO
           
           //Pieds
            //$pdf->Text(0, 0, 'ff');
//           $pdf->Ln(41);
//           $pdf->Cell(0,4,utf8_decode('Facturation (Document): Par 480 KO'),0,1,'R');
           $pdf->SetFont('Helvetica','', 8);
           $pdf->Cell(0,4,utf8_decode('LSD (Les Spécialistes de la Dématérialisation), MBAO VILLE NEUVE, Tél: 76 462 20 14 / Email : samacoffre@samacoffre.sn'),0,1,'C');
           $pdf->Cell(0,3,'SN DKR 2021 A 30776-NINEA 008864606',0,1,'C');

            
           
           
           
            
            return new Response($pdf->Output(), 200, array(
            'Content-Type' => 'application/pdf'));
    }

    /**
     * Displays a form to edit an existing certificat entity.
     *
     * @Route("/{id}/edit", name="certificat_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Certificat $certificat)
    {
        $deleteForm = $this->createDeleteForm($certificat);
        $editForm = $this->createForm('AppBundle\Form\CertificatType', $certificat);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('certificat_edit', array('id' => $certificat->getId()));
        }

        return $this->render('certificat/edit.html.twig', array(
            'certificat' => $certificat,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a certificat entity.
     *
     * @Route("/{id}", name="certificat_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Certificat $certificat)
    {
        $form = $this->createDeleteForm($certificat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($certificat);
            $em->flush();
        }

        return $this->redirectToRoute('certificat_index');
    }

    /**
     * Creates a form to delete a certificat entity.
     *
     * @param Certificat $certificat The certificat entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Certificat $certificat)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('certificat_delete', array('id' => $certificat->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
    
    
    
    function bissextile($annee) {
	if( (is_int($annee/4) && !is_int($annee/100)) || is_int($annee/400)) {
		// Année bissextile
		// vous remplacez le retour par ce que vou voulez
		return TRUE;
	} else {
		// Année NON bissextile
		// vous remplacez le retour par ce que vou voulez
		return FALSE;
	}
    }
    
    
    /**
     * 
     * @param type $montant
     * @return string
     */
    public function inserEspace($montant){
           
           $returnvalue=$montant;
           if(strlen($montant)==4){
             $returnvalue= substr($montant, 0,1).' '.substr($montant, 1);
           } elseif (strlen($montant)==5) {
           $returnvalue= substr($montant, 0,2).' '.substr($montant, 2);
           } elseif (strlen($montant)==6) {
           $returnvalue= substr($montant, 0,3).' '.substr($montant, 3);
           }  elseif (strlen($montant)==7) {
           $returnvalue= substr($montant, 0,1).' '.substr($montant, 1,3).' '.substr($montant, 4);
           }  elseif (strlen($montant)==8) {
           $returnvalue= substr($montant, 0,2).' '.substr($montant, 2,3).' '.substr($montant, 5);
           }  elseif (strlen($montant)==9) {
           $returnvalue= substr($montant, 0,3).' '.substr($montant, 3,3).' '.substr($montant, 6);
           }  elseif (strlen($montant)==10) {
           $returnvalue= substr($montant, 0,1).' '.substr($montant, 1,3).' '.substr($montant, 4,3).' '.substr($montant, 7);
           } elseif (strlen($montant)==11) {
           $returnvalue= substr($montant, 0,2).' '.substr($montant, 2,3).' '.substr($montant, 5,3).' '.substr($montant, 8);
           }  elseif (strlen($montant)==12) {
           $returnvalue= substr($montant, 0,3).' '.substr($montant, 3,3).' '.substr($montant, 6,3).' '.substr($montant, 9);
           }  
            
            return $returnvalue;
    }
}
