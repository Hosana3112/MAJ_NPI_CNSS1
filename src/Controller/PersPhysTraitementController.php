<?php

namespace App\Controller;

use App\Entity\PersPhys;
use App\Entity\PersPhysTraitement;
use App\Form\PersPhysTraitementForm;
use App\Form\RefusForm;
use App\Repository\PersPhysTraitementRepository;
use App\Repository\PersPhysRepository;
use App\Repository\RnppRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/pers/phys/traitement')]
final class PersPhysTraitementController extends AbstractController
{

    #[Route(name: 'app_pers_phys_traitement_index', methods: ['GET'])]
    public function index(PersPhysTraitementRepository $persPhysTraitementRepository): Response
    {
        return $this->render('pers_phys_traitement/index.html.twig', [
            'pers_phys_traitements' => $persPhysTraitementRepository->findAll(),
        ]);
    }

   
    #[Route('/show/{npi}', name: 'app_pers_phys_traitement_show', methods: ['GET'])]
    public function show(string $npi, PersPhysTraitementRepository $repository): Response
    {
        $persPhysTraitement = $repository->findOneBy(['npi' => $npi]);
        return $this->render('pers_phys_traitement/show.html.twig', [
            'pers_phys_traitement' => $persPhysTraitement,
        ]);
    }

     #[Route( '/valider/{npi}', name: 'app_pers_phys_traitement_valider', methods: ['GET'])]
    public function valider(string $npi, Request $request, EntityManagerInterface $entityManager,MailerInterface $mailer,
    PersPhysTraitementRepository $persphystraitementrepository,
    RnppRepository $rnpprepository,PersPhysRepository $persphysrepository): Response
    {
       $rnpp=$rnpprepository->findOneBy(['npi'=>$npi]);
       $travailleur=$persphystraitementrepository->findOneBy(['npi'=>$npi]);
        //Générer un nouveau numéro 
        $NewNumCNSS = random_int(100000, 999999);

        $email =$rnpp->getEmail();
        $matpers=$travailleur->getNumCNSS();
        $session = $request->getSession();
            $session->set('email', $email);
            $session->set('NumCNSS', $NewNumCNSS);
            $session->set('mat_pers', $matpers);

        $travailleur->setStatut('Terminé');
        $travailleur->setDatetraitement(new \DateTime());

        $persphy=$persphysrepository->findOneBy(['mat_pers'=> $session->get('mat_pers')]);
        $persphy->setNpi($npi);
        $persphy->setNumCNSS($NewNumCNSS);

        
        $entityManager->flush();

        try {
            $emailObj = (new Email())
               ->from('tounde.abdou@yahoo.com')
               ->to($email)
               ->subject('Validation des informations')
               ->text("Votre NPI a bien été consolidé. Votre nouveau numéro attribué est :$NewNumCNSS");

               $mailer->send($emailObj);

               return $this->redirectToRoute('app_pers_phys_traitement_index', [], Response::HTTP_SEE_OTHER);

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de l’envoi du mail : ' . $e->getMessage());
             // Optionnel : logger l'erreur
        }
         return $this->redirectToRoute('app_pers_phys_traitement_index', [], Response::HTTP_SEE_OTHER);
    

    }

    #[Route( '/refuser/{npi}', name: 'app_pers_phys_traitement_refuser', methods: ['GET','POST'])]
    public function entrer(string $npi, Request $request, EntityManagerInterface $entityManager,MailerInterface $mailer, 
    PersPhysTraitementRepository $persphystraitementrepository,
    RnppRepository $rnpprepository,PersPhysRepository $persphysrepository): Response
    {
        $traitement = $persphystraitementrepository->findOneBy(['npi' => $npi]);
        $rnpp = $rnpprepository->findOneBy(['npi' => $npi]);
 
        if (!$traitement || !$rnpp) {
           throw $this->createNotFoundException('Données introuvables.');
        }

        $form = $this->createForm(RefusForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           $motif = $form->get('motif')->getData();

           try {
             $email = (new Email())
                ->from('tounde.abdou@yahoo.com')
                ->to($rnpp->getEmail())
                ->subject('Refus de traitement de votre NPI')
                ->text("Votre demande a été refusée. Motif :\n\n" . $motif);

                $mailer->send($email);

                $traitement->setStatut('Rejeté');
                $traitement->setMotif($motif);
                $traitement->setDatetraitement(new \DateTime());
                $entityManager->flush();
 
                $this->addFlash('success', 'Refus enregistré avec succès.');


                return $this->redirectToRoute('app_pers_phys_traitement_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de l’envoi de l’e-mail : ' . $e->getMessage());
            }
        }

        return $this->render('pers_phys_refus/refus.html.twig', [
           'form' => $form->createView(),
           'npi' => $npi,
        ]);
    }

    #[Route('/stat', name: 'app_pers_phys_traitement_sstat', methods: ['GET','POST'])]
    #[IsGranted('ROLE_ADMIN')]
public function stat( PersPhysTraitementRepository $persphystraitementrepository, PersPhysRepository $persphysrepository): Response
{

    $nbDemandes = $persphystraitementrepository->count([]);
    $nbAvecNpi = $persphysrepository->createQueryBuilder('p')
        ->select('count(p.id)')
        ->where('p.npi IS NOT NULL')
        ->andWhere('p.npi <> \'\'')
        ->getQuery()
        ->getSingleScalarResult();

    $nbSansNpi = $persphysrepository->createQueryBuilder('p')
        ->select('count(p.id)')
        ->where('p.npi IS NULL OR p.npi = \'\'')
        ->getQuery()
        ->getSingleScalarResult();

    return $this->render('pers_phys_stat/stat.html.twig', [
        'nbDemandes' => $nbDemandes,
        'nbAvecNpi' => $nbAvecNpi,
        'nbSansNpi' => $nbSansNpi,
    ]);
}


}
