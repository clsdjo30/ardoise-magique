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
use App\Repository\CarteRepository;
use App\Repository\PlatVariantRepository;
use App\Service\Carte\CartePreviewService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[Route('/admin/wizard/carte')]
class CarteWizardController extends AbstractController
{
    private const SESSION_KEY = 'carte_wizard_data';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private CarteRepository $carteRepository,
        private PlatVariantRepository $variantRepository,
        private CartePreviewService $previewService,
        private UrlGeneratorInterface $urlGenerator
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
        $carteId = $request->query->getInt('carteId', 0);

        // Check if we're editing an existing carte
        if ($carteId > 0 && !$session->has(self::SESSION_KEY)) {
            // Load existing carte data
            $wizardData = $this->loadCarteIntoWizard($carteId);
            $session->set(self::SESSION_KEY, $wizardData);
        } else {
            $wizardData = $session->get(self::SESSION_KEY);

            if (!$wizardData instanceof CarteWizardData) {
                $wizardData = new CarteWizardData();
                $session->set(self::SESSION_KEY, $wizardData);
            }
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

        $form = $this->createForm(Step2SectionsType::class, $wizardData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

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

            // Process drag-and-drop positions from the form
            $this->processDragAndDropPositions($request, $wizardData);

            // Check if we're editing or creating
            $isEdit = $wizardData->carteId !== null;

            if ($isEdit) {
                // Update existing carte
                $carte = $this->updateCarteFromWizardData($wizardData);
            } else {
                // Create new carte
                $carte = $this->createCarteFromWizardData($wizardData);
                $this->entityManager->persist($carte);
            }

            $this->entityManager->flush();

            try {
                // Generate public URL using the restaurant's owner slug
                $restaurantOwnerSlug = $carte->getRestaurant()->getOwner()->getSlug();
                $publicUrl = $this->urlGenerator->generate('app_show_carte', [
                    'restaurant' => $restaurantOwnerSlug,
                    'slug' => $carte->getSlug()
                ], UrlGeneratorInterface::ABSOLUTE_URL);

                // Add success flash message with link
                if ($isEdit) {
                    $this->addFlash('success', sprintf(
                        'Carte "%s" mise à jour avec succès ! <a href="%s" target="_blank" class="alert-link text-decoration-underline">Voir la carte</a>',
                        $carte->getName(),
                        $publicUrl
                    ));
                } else {
                    $this->addFlash('success', sprintf(
                        'Carte "%s" créée avec succès ! <a href="%s" target="_blank" class="alert-link text-decoration-underline">Voir la carte</a>',
                        $carte->getName(),
                        $publicUrl
                    ));
                }
            } catch (\Exception $e) {
                // If URL generation fails, still show a message
                $this->addFlash('warning', sprintf(
                    'Carte "%s" %s avec succès ! (Erreur lors de la génération du lien: %s)',
                    $carte->getName(),
                    $isEdit ? 'mise à jour' : 'créée',
                    $e->getMessage()
                ));
            }

            // Clear wizard session data
            $request->getSession()->remove(self::SESSION_KEY);

            return $this->redirectToRoute('admin', ['restaurant' => $carte->getRestaurant()->getOwner()->getSlug()]);
        }

        $isEditMode = $wizardData->carteId !== null;

        return $this->render('carte_wizard/wizard.html.twig', [
            'form' => null,
            'currentStep' => 'step4',
            'wizardData' => $wizardData,
            'previewData' => $previewData,
            'stepTitle' => $isEditMode ? 'Aperçu de votre carte modifiée' : 'Aperçu de votre carte',
            'stepDescription' => $isEditMode ? 'Vérifiez les modifications avant de sauvegarder' : 'Vérifiez les informations avant de créer votre carte',
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
        $carte->setName($data->name);
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

    private function updateCarteFromWizardData(CarteWizardData $data): Carte
    {
        $carte = $this->carteRepository->find($data->carteId);

        if (!$carte) {
            throw $this->createNotFoundException('Carte non trouvée');
        }

        // Update basic properties
        $carte->setName($data->name);
        $carte->setValidFrom($data->validFrom);
        $carte->setValidTo($data->validTo);
        $carte->setRestaurant($data->restaurant);
        $carte->setIsPublished($data->isPublished);

        // Remove all existing sections (cascade will handle items)
        foreach ($carte->getSections()->toArray() as $section) {
            $carte->removeSection($section);
        }

        // Add new sections
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
     * Process drag-and-drop positions from Step 4 form submission
     */
    private function processDragAndDropPositions(Request $request, CarteWizardData $wizardData): void
    {
        $sectionsData = $request->request->all('sections');
        if (empty($sectionsData)) {
            return;
        }

        // Create a mapping of old index to new position
        $sectionPositions = [];
        foreach ($sectionsData as $oldIndex => $sectionSubmit) {
            $newPosition = (int)($sectionSubmit['position'] ?? $oldIndex);
            $sectionPositions[$oldIndex] = $newPosition;
        }

        // Sort sections by their new positions
        $sortedSections = $wizardData->sections;
        usort($sortedSections, function($a, $b) use ($sectionPositions, $wizardData) {
            $indexA = array_search($a, $wizardData->sections, true);
            $indexB = array_search($b, $wizardData->sections, true);
            return ($sectionPositions[$indexA] ?? 0) <=> ($sectionPositions[$indexB] ?? 0);
        });

        // Reorganize selected variants according to new section order and item positions
        $newSelectedVariants = [];
        foreach ($sortedSections as $newSectionIndex => $section) {
            $oldSectionIndex = array_search($section, $wizardData->sections, true);

            // Get items data for this section
            $itemsData = $sectionsData[$oldSectionIndex]['items'] ?? [];

            // Sort items by position
            $sortedItems = [];
            foreach ($itemsData as $itemData) {
                $position = (int)($itemData['position'] ?? 0);
                $variantId = (int)($itemData['variantId'] ?? 0);
                if ($variantId > 0) {
                    $sortedItems[$position] = $variantId;
                }
            }
            ksort($sortedItems);

            $newSelectedVariants[$newSectionIndex] = array_values($sortedItems);
        }

        // Update wizard data
        $wizardData->sections = $sortedSections;
        $wizardData->selectedVariants = $newSelectedVariants;

        // Update section positions
        foreach ($wizardData->sections as $index => $section) {
            $section->position = $index;
        }
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

    /**
     * Load an existing carte into wizard data for editing
     */
    private function loadCarteIntoWizard(int $carteId): CarteWizardData
    {
        $carte = $this->carteRepository->find($carteId);

        if (!$carte) {
            throw $this->createNotFoundException('Carte non trouvée');
        }

        /** @var User $user */
        $user = $this->getUser();

        // Security check: user must own the restaurant
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && $carte->getRestaurant()->getOwner() !== $user) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette carte');
        }

        // Create wizard data from carte
        $wizardData = new CarteWizardData();
        $wizardData->carteId = $carte->getId();
        $wizardData->name = $carte->getName();
        $wizardData->validFrom = $carte->getValidFrom();
        $wizardData->validTo = $carte->getValidTo();
        $wizardData->restaurant = $carte->getRestaurant();
        $wizardData->isPublished = $carte->isPublished();

        // Convert sections
        foreach ($carte->getSections() as $carteSection) {
            $sectionData = new CarteSectionData();
            $sectionData->position = $carteSection->getPosition();
            $sectionData->description = $carteSection->getDescription();

            // Find PlatCategorie by titre
            // Note: This assumes we can match by titre. If this is not reliable,
            // you may need to store the categorie_id in CarteSection
            $categorie = $this->entityManager->getRepository(\App\Entity\PlatCategorie::class)
                ->findOneBy(['titre' => $carteSection->getTitre()]);

            if ($categorie) {
                $sectionData->categorie = $categorie;
            }

            $wizardData->sections[] = $sectionData;

            // Store selected variants for this section
            $variantIds = [];
            foreach ($carteSection->getItems() as $item) {
                $variantIds[] = $item->getPlatVariant()->getId();
            }
            $wizardData->selectedVariants[$sectionData->position] = $variantIds;
        }

        return $wizardData;
    }
}
