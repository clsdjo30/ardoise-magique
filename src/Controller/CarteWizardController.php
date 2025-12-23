<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Carte;
use App\Entity\CarteSection;
use App\Entity\SectionItem;
use App\Entity\User;
use App\Form\CarteWizard\CarteWizardData;
use App\Form\CarteWizard\CarteSectionData;
use App\Form\CarteWizard\Step1GeneralInfoType;
use App\Form\CarteWizard\Step2SectionsType;
use App\Repository\PlatVariantRepository;
use App\Service\Carte\CartePreviewService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[Route('/admin/wizard/carte')]
class CarteWizardController extends AbstractController
{
    private const SESSION_KEY = 'carte_wizard_data';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private PlatVariantRepository $variantRepository,
        private CartePreviewService $previewService
    ) {
    }

    #[Route('', name: 'app_carte_wizard')]
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // PREMIUM subscription check
        $this->denyAccessUnlessGranted('CARTE_CREATE');

        // Get or create wizard data from session
        $session = $request->getSession();
        $wizardData = $session->get(self::SESSION_KEY);

        if (!$wizardData instanceof CarteWizardData) {
            $wizardData = new CarteWizardData();
            $session->set(self::SESSION_KEY, $wizardData);
        }

        // Re-attach detached entities from session
        $this->reattachEntities($wizardData);

        // Determine current step
        $currentStep = $request->query->get('step', 'step1');
        $wizardData->currentStep = $currentStep;

        return match ($currentStep) {
            'step1' => $this->handleStep1($request, $wizardData),
            'step2' => $this->handleStep2($request, $wizardData),
            'step3' => $this->handleStep3($request, $wizardData),
            'step4' => $this->handleStep4($request, $wizardData),
            default => $this->handleStep1($request, $wizardData),
        };
    }

    private function handleStep1(Request $request, CarteWizardData $wizardData): Response
    {
        $form = $this->createForm(Step1GeneralInfoType::class, $wizardData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Save to session and go to next step
            $request->getSession()->set(self::SESSION_KEY, $wizardData);
            return $this->redirectToRoute('app_carte_wizard', ['step' => 'step2']);
        }

        return $this->render('carte_wizard/wizard.html.twig', [
            'form' => $form->createView(),
            'currentStep' => 'step1',
            'wizardData' => $wizardData,
            'stepTitle' => 'Informations générales de la carte',
            'stepDescription' => 'Définissez les informations de base de votre carte restaurant',
        ]);
    }

    private function handleStep2(Request $request, CarteWizardData $wizardData): Response
    {
        // Initialize sections if empty - start with one empty section
        if (empty($wizardData->sections)) {
            $section = new CarteSectionData();
            $section->position = 0;
            $wizardData->sections[] = $section;
        }

        dump('BEFORE FORM: sections count = ' . count($wizardData->sections));
        foreach ($wizardData->sections as $i => $s) {
            dump("Section $i: " . ($s->categorie?->getTitre() ?? 'no category'));
        }

        $form = $this->createForm(Step2SectionsType::class, $wizardData);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            dump('RAW POST DATA:', $request->request->all());
        }

        if ($form->isSubmitted() && $form->isValid()) {
            dump('AFTER FORM: sections count = ' . count($wizardData->sections));
            foreach ($wizardData->sections as $i => $s) {
                dump("Section $i: " . ($s->categorie?->getTitre() ?? 'no category'));
            }

            // Update positions
            foreach ($wizardData->sections as $index => $section) {
                $section->position = $index;
            }

            // Save to session and go to next step
            $request->getSession()->set(self::SESSION_KEY, $wizardData);
            return $this->redirectToRoute('app_carte_wizard', ['step' => 'step3']);
        }

        return $this->render('carte_wizard/wizard.html.twig', [
            'form' => $form->createView(),
            'currentStep' => 'step2',
            'wizardData' => $wizardData,
            'stepTitle' => 'Créer les sections de votre carte',
            'stepDescription' => 'Organisez votre carte en sections (Entrées, Plats, Desserts, etc.)',
        ]);
    }

    private function handleStep3(Request $request, CarteWizardData $wizardData): Response
    {
        dump('STEP 3: sections count = ' . count($wizardData->sections));
        foreach ($wizardData->sections as $i => $s) {
            dump("Section $i: " . ($s->categorie?->getTitre() ?? 'no category'));
        }

        if ($request->isMethod('POST')) {
            // Get selected variants from POST data
            $selectedVariants = $request->request->all('selectedVariants');

            // Store selected variants in wizard data
            if ($selectedVariants) {
                $wizardData->selectedVariants = [];
                foreach ($selectedVariants as $sectionIndex => $variantIds) {
                    $wizardData->selectedVariants[(int)$sectionIndex] = array_map('intval', $variantIds);
                }
            }

            // Save to session and go to next step
            $request->getSession()->set(self::SESSION_KEY, $wizardData);
            return $this->redirectToRoute('app_carte_wizard', ['step' => 'step4']);
        }

        return $this->render('carte_wizard/wizard.html.twig', [
            'form' => null,
            'currentStep' => 'step3',
            'wizardData' => $wizardData,
            'stepTitle' => 'Ajouter les plats à vos sections',
            'stepDescription' => 'Sélectionnez les plats de votre catalogue pour chaque section',
        ]);
    }

    private function handleStep4(Request $request, CarteWizardData $wizardData): Response
    {
        // Generate preview data
        $previewData = $this->previewService->generatePreviewData($wizardData);

        if ($request->isMethod('POST')) {
            // Update isPublished from form
            $wizardData->isPublished = $request->request->getBoolean('isPublished', false);

            // Create carte from wizard data
            $carte = $this->createCarteFromWizardData($wizardData);

            $this->entityManager->persist($carte);
            $this->entityManager->flush();

            // Clear session
            $request->getSession()->remove(self::SESSION_KEY);

            $this->addFlash('success', 'Carte créée avec succès !');
            return $this->redirectToRoute('admin', ['restaurant' => $this->getUser()->getSlug()]);
        }

        return $this->render('carte_wizard/wizard.html.twig', [
            'form' => null,
            'currentStep' => 'step4',
            'wizardData' => $wizardData,
            'previewData' => $previewData,
            'stepTitle' => 'Aperçu de votre carte',
            'stepDescription' => 'Vérifiez les informations avant de créer votre carte',
        ]);
    }

    #[Route('/cancel', name: 'app_carte_wizard_cancel')]
    public function cancel(Request $request): Response
    {
        // Clear session
        $request->getSession()->remove(self::SESSION_KEY);

        $this->addFlash('info', 'Création de carte annulée.');
        return $this->redirectToRoute('admin', ['restaurant' => $this->getUser()->getSlug()]);
    }

    private function createCarteFromWizardData(CarteWizardData $data): Carte
    {
        $carte = new Carte();
        $carte->setValidFrom($data->validFrom);
        $carte->setValidTo($data->validTo);
        $carte->setRestaurant($data->restaurant);
        $carte->setIsPublished($data->isPublished);

        // Generate slug
        $slugger = new AsciiSlugger();
        $baseSlug = $slugger->slug(
            $data->restaurant->getName() . ' carte ' . $data->validFrom->format('Y-m-d')
        )->lower()->toString();
        $carte->setSlug($baseSlug . '-' . uniqid());

        // Create sections
        foreach ($data->sections as $index => $sectionData) {
            $section = new CarteSection();
            $section->setTitre($sectionData->categorie->getTitre());
            $section->setDescription($sectionData->description);
            $section->setPosition($index);
            $section->setCarte($carte);

            // Add items to section
            $variantIds = $data->selectedVariants[$index] ?? [];
            foreach ($variantIds as $position => $variantId) {
                $variant = $this->variantRepository->find($variantId);
                if ($variant) {
                    $item = new SectionItem();
                    $item->setPosition($position);
                    $item->setCarteSection($section);
                    $item->setPlatVariant($variant);

                    $section->addItem($item);
                }
            }

            $carte->addSection($section);
        }

        return $carte;
    }

    /**
     * Re-attach detached entities from session to EntityManager
     */
    private function reattachEntities(CarteWizardData $wizardData): void
    {
        // Re-attach Restaurant entity by refetching from database
        if ($wizardData->restaurant && !$this->entityManager->contains($wizardData->restaurant)) {
            $restaurantId = $wizardData->restaurant->getId();
            if ($restaurantId) {
                $wizardData->restaurant = $this->entityManager->find(
                    \App\Entity\Restaurant::class,
                    $restaurantId
                );
            }
        }

        // Re-attach PlatCategorie entities in sections
        foreach ($wizardData->sections as $section) {
            if ($section->categorie && !$this->entityManager->contains($section->categorie)) {
                $categorieId = $section->categorie->getId();
                if ($categorieId) {
                    $section->categorie = $this->entityManager->find(
                        \App\Entity\PlatCategorie::class,
                        $categorieId
                    );
                }
            }
        }
    }
}
