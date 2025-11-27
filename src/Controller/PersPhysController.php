<?php

namespace App\Controller;

use App\Entity\PersPhys;
use App\Entity\PersPhysTraitement;
use App\Form\OtpFormType;
use App\Form\PersPhysForm;
use App\Form\PersPhysFormRech;
use App\Form\PersPhysFormVerif;
use App\Form\RecapForm;
use App\Repository\PersPhysRepository;
use App\Repository\PersPhysTraitementRepository;
use App\Repository\RnppRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[Route('/pers/phys')]
final class PersPhysController extends AbstractController
{
    #[Route(name: 'app_pers_phys_index', methods: ['GET'])]
    public function index(PersPhysRepository $persPhysRepository): Response
    {
        return $this->render('pers_phys/index.html.twig', [
            'pers_phys' => $persPhysRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_pers_phys_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $persPhy = new PersPhys();
        $form = $this->createForm(PersPhysForm::class, $persPhy);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($persPhy);
            $entityManager->flush();

            return $this->redirectToRoute('app_pers_phys_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pers_phys/new.html.twig', [
            'pers_phy' => $persPhy,
            'form' => $form,
        ]);
    }

    #[Route('/rech', name: 'app_pers_phys_rech', methods: ['GET', 'POST'])]
public function rech(
    Request $request,
    EntityManagerInterface $entityManager,
    RnppRepository $rnppRepository,
    PersPhysRepository $persPhysRepository,
    MailerInterface $mailer,
    PersPhysTraitementRepository $persphystraitementrepository
): Response {
    $persPhy = new PersPhys();
    $form = $this->createForm(PersPhysFormRech::class, $persPhy);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $npi = $form->get('npi')->getData();

        // Vérifier si le NPI existe à l'ANIP
        $personneANIP = $rnppRepository->findOneBy(['npi' => $npi]);

        if ($personneANIP === null) {
            $this->addFlash('error', 'NPI saisi incorrect.');
            return $this->render('pers_phys_rech/rech.html.twig', [
                'pers_phy' => $persPhy,
                'form' => $form,
            ]);
        }

        // Vérifier si le NPI est déjà dans la table traitement
        $traitement = $persphystraitementrepository->findOneBy(['npi' => $npi]);

        if ($traitement !== null) {
            $statut = $traitement->getStatut();

            if ($statut === 'En cours') {
                $this->addFlash('error', 'Votre demande est en cours de traitement.');
                return $this->render('pers_phys_rech/rech.html.twig', [
                    'pers_phy' => $persPhy,
                    'form' => $form,
                ]);
            }

            if ($statut === 'Terminé') {
                // Vérifier dans la base CNSS
                $personneCNSS = $persPhysRepository->findOneBy(['npi' => $npi]);
                if ($personneCNSS !== null) {
                    $this->addFlash('error', 'NPI déjà consolidé.');
                    return $this->render('pers_phys_rech/rech.html.twig', [
                        'pers_phy' => $persPhy,
                        'form' => $form,
                    ]);
                }
            }

            // ⚠️ Si statut = Rejeté → on ne fait rien ici
            // La suite de la procédure (OTP, saisie) se déroulera normalement
        }

        // Envoi de l’OTP (Rejeté ou nouveau)
        $email = $personneANIP->getEmail();
        $otp = random_int(100000, 999999);

        $session = $request->getSession();
        $session->set('otp_code', $otp);
        $session->set('otp_email', $email);
        $session->set('npi', $npi);

        try {
            $emailObj = (new Email())
                ->from('tounde.abdou@yahoo.com')
                ->to($email)
                ->subject('Votre code de vérification')
                ->text("Votre code OTP est : $otp");

            $mailer->send($emailObj);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de l’envoi du mail : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_otp_verify');
    }

    return $this->render('pers_phys_rech/rech.html.twig', [
        'pers_phy' => $persPhy,
        'form' => $form,
    ]);
}

    #[Route('/otp-verify', name: 'app_otp_verify')]
    public function verifyOtp(Request $request): Response
    {
        $form = $this->createForm(OtpFormType::class);
        $form->handleRequest($request);

        $session = $request->getSession();
        $otpExpected = $session->get('otp_code');
        $npi = $session->get('npi');
        $session->set('npi',$npi);

        if ($form->isSubmitted() && $form->isValid()) {
            $otpEntered = $form->get('otp')->getData();

            if ($otpEntered == $otpExpected) {
                // Authentification validée, redirection
               // $this->addFlash('success', 'OTP validé avec succès.');
                // Rediriger vers la page où l'utilisateur va saisir son numéro CNSS et sa date de naissance

             return $this->redirectToRoute('app_pers_phys_verif');
            } else {
                $this->addFlash('error', 'Code OTP incorrect.');
            }
        }

        return $this->render('security/verify_otp.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/verif', name: 'app_pers_phys_verif', methods: ['GET', 'POST'])]
    public function verif(Request $request, EntityManagerInterface $entityManager,PersPhysRepository $persPhysRepository): Response
    {
        $persPhy = new PersPhys();
        $form = $this->createForm(PersPhysFormVerif::class, $persPhy);
        $form->handleRequest($request);
        $session = $request->getSession();
        $npi = $session->get('npi');
        $session->set('npi',$npi);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération du numéro d'assurance du travailleur et de sa date de naissance
            $matpers = $form->get('mat_pers')->getData();
            $dateNais = $form->get('dateNaiss')->getData();
            // Vérification de l'existence de ce travailleur à la CNSS
            $personneCNSS = $persPhysRepository->findOneBy([
                'mat_pers' => $matpers,
                'dateNaiss' => $dateNais,
            ]);
            if ($personneCNSS === null) {
                $this->addFlash('error', 'Numéro d\'assurance ou date de naissance incorrect .');
            } else {
                // Le travailleur est trouvé, on affiche le récapitulatif des infos
                $session->set('mat_pers',$matpers);

                return $this->redirectToRoute('app_pers_phys_recap', [], Response::HTTP_SEE_OTHER);
            }

        }
        return $this->render('pers_phys_verif/verif.html.twig', [
            'pers_phy' => $persPhy,
            'form' => $form,
        ]);
    }

   #[Route('/recap', name: 'app_pers_phys_recap', methods: ['GET','POST'])]
public function recap(
    Request $request,
    PersPhysRepository $persPhysRepository,
    EntityManagerInterface $entityManager,
    RnppRepository $rnpprepository,
    PersPhysTraitementRepository $persphystraitementrepository
): Response {
    $session = $request->getSession();
    $npi = $session->get('npi');
    $matpers = $session->get('mat_pers');

    $personneRecap = $persPhysRepository->findOneBy(['mat_pers' => $matpers]);
    $rnpp = $rnpprepository->findOneBy(['npi' => $npi]);

    $persPhy = new PersPhys();
    $persPhy->setMatPers($matpers);
    $persPhy->setNpi($npi);
    $persPhy->setNomPers($personneRecap->getNomPers());
    $persPhy->setPnomPers($personneRecap->getPnomPers());
    $persPhy->setDateNaiss($personneRecap->getDateNaiss());

    $form = $this->createForm(RecapForm::class, $persPhy);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $donneesForm = $form->getData();

        if (
            $donneesForm->getNomPers() === $personneRecap->getNomPers() &&
            $donneesForm->getPnomPers() === $personneRecap->getPnomPers() &&
            $donneesForm->getDateNaiss() == $personneRecap->getDateNaiss() &&
            $donneesForm->getNpi() === $npi
        ) {
            // 🔍 Vérifier si une demande rejetée existe
            $persphystraitement = $persphystraitementrepository->findOneBy([
                'npi' => $npi,
                'statut' => 'Rejeté',
            ]);

            if ($persphystraitement) {
                // 🛠️ Mise à jour de la ligne existante
                $persphystraitement->setStatut('En cours');
                $persphystraitement->setNumCNSS($personneRecap->getMatPers());
                $persphystraitement->setNomCNSS($personneRecap->getNomPers());
                $persphystraitement->setPrenomCNSS($personneRecap->getPnomPers());
                $persphystraitement->setDateNaissance($personneRecap->getDateNaiss());
                $persphystraitement->setNomANIP($rnpp->getNom());
                $persphystraitement->setPrenomANIP($rnpp->getPrenom());
                $persphystraitement->setDatedemande(new \DateTime());

                $this->addFlash('success', 'Votre demande a été relancée avec succès.');

            } else {
                // ➕ Création d'une nouvelle demande
                $persphystraitement = new PersPhysTraitement();
                $persphystraitement->setNPI($npi);
                $persphystraitement->setNumCNSS($personneRecap->getMatPers());
                $persphystraitement->setNomCNSS($personneRecap->getNomPers());
                $persphystraitement->setPrenomCNSS($personneRecap->getPnomPers());
                $persphystraitement->setDateNaissance($personneRecap->getDateNaiss());
                $persphystraitement->setStatut('En cours');
                $persphystraitement->setNomANIP($rnpp->getNom());
                $persphystraitement->setPrenomANIP($rnpp->getPrenom());
                $persphystraitement->setDatedemande(new \DateTime());

                $entityManager->persist($persphystraitement);
                $this->addFlash('success', 'Votre demande a été envoyée à la CNSS.');
            }
            $entityManager->flush();
            return $this->redirectToRoute('app_pers_phys_rech', [], Response::HTTP_SEE_OTHER);
        } else {
            $this->addFlash('error', 'Les informations saisies ne correspondent pas.');
        }
    }
    return $this->render('pers_phys_recap/recap.html.twig', [
        'form' => $form->createView(),
    ]);
}


    #[Route('view/{id}', name: 'app_pers_phys_show', methods: ['GET'])]
    public function show(PersPhys $persPhy): Response
    {
        return $this->render('pers_phys/show.html.twig', [
            'pers_phy' => $persPhy,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_pers_phys_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PersPhys $persPhy, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PersPhysForm::class, $persPhy);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_pers_phys_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pers_phys/edit.html.twig', [
            'pers_phy' => $persPhy,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_pers_phys_delete', methods: ['POST'])]
    public function delete(Request $request, PersPhys $persPhy, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$persPhy->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($persPhy);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_pers_phys_index', [], Response::HTTP_SEE_OTHER);
    }
}
