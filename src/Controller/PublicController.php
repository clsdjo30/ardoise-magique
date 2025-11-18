<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\UserRepository;
use Mpdf\Mpdf;
use Spatie\PdfToImage\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

class PublicController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    #[Route('/ardoise/{slug}', name: 'app_public_web', methods: ['GET'])]
    public function showWeb(string $slug): Response
    {
        $user = $this->userRepository->findOneBy(['slug' => $slug]);

        if (!$user) {
            throw $this->createNotFoundException('Restaurant non trouvé');
        }

        // Récupérer l'ardoise active
        $ardoise = null;
        foreach ($user->getArdoises() as $a) {
            if ($a->isActive()) {
                $ardoise = $a;
                break;
            }
        }

        if (!$ardoise) {
            return $this->render('public/no_active.html.twig', [
                'restaurant' => $user->getNomRestaurant(),
            ]);
        }

        return $this->render('public/show_web.html.twig', [
            'ardoise' => $ardoise,
            'user' => $user,
        ]);
    }

    #[Route('/ardoise/{slug}/pdf', name: 'app_public_pdf', methods: ['GET'])]
    public function showPdf(string $slug): Response
    {
        $user = $this->userRepository->findOneBy(['slug' => $slug]);

        if (!$user) {
            throw $this->createNotFoundException('Restaurant non trouvé');
        }

        // Récupérer l'ardoise active
        $ardoise = null;
        foreach ($user->getArdoises() as $a) {
            if ($a->isActive()) {
                $ardoise = $a;
                break;
            }
        }

        if (!$ardoise) {
            throw $this->createNotFoundException('Aucune ardoise active pour ce restaurant');
        }

        // Générer le HTML
        $html = $this->renderView('public/show_pdf.html.twig', [
            'ardoise' => $ardoise,
            'user' => $user,
        ]);

        // Créer le PDF avec mPDF
        $mpdf = new Mpdf([
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'default_font' => 'dejavusans',
        ]);

        $mpdf->WriteHTML($html);

        // Retourner le PDF
        return new Response(
            $mpdf->Output('', 'S'),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="ardoise-' . $slug . '.pdf"',
            ]
        );
    }

    #[Route('/ardoise/{slug}/image', name: 'app_public_image', methods: ['GET'])]
    public function showImage(string $slug): Response
    {
        $user = $this->userRepository->findOneBy(['slug' => $slug]);

        if (!$user) {
            throw $this->createNotFoundException('Restaurant non trouvé');
        }

        // Récupérer l'ardoise active
        $ardoise = null;
        foreach ($user->getArdoises() as $a) {
            if ($a->isActive()) {
                $ardoise = $a;
                break;
            }
        }

        if (!$ardoise) {
            throw $this->createNotFoundException('Aucune ardoise active pour ce restaurant');
        }

        // Vérifier si imagick est disponible
        if (!extension_loaded('imagick')) {
            // Si imagick n'est pas disponible, retourner le PDF à la place
            $this->addFlash('warning', 'La génération d\'image nécessite l\'extension imagick. Le PDF sera téléchargé à la place.');
            return $this->forward('App\Controller\PublicController::showPdf', [
                'slug' => $slug,
            ]);
        }

        // Générer le HTML
        $html = $this->renderView('public/show_pdf.html.twig', [
            'ardoise' => $ardoise,
            'user' => $user,
        ]);

        // Créer un fichier PDF temporaire
        $tempPdfPath = sys_get_temp_dir() . '/' . uniqid('ardoise_') . '.pdf';

        $mpdf = new Mpdf([
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'default_font' => 'dejavusans',
        ]);

        $mpdf->WriteHTML($html);
        $mpdf->Output($tempPdfPath, 'F');

        // Convertir le PDF en image
        $tempImagePath = sys_get_temp_dir() . '/' . uniqid('ardoise_') . '.jpg';

        $pdf = new Pdf($tempPdfPath);
        $pdf->setResolution(150) // DPI pour Instagram
            ->saveImage($tempImagePath);

        // Supprimer le fichier PDF temporaire
        @unlink($tempPdfPath);

        // Créer la réponse avec le fichier image
        $response = new BinaryFileResponse($tempImagePath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'ardoise-' . $slug . '-' . date('Y-m-d') . '.jpg'
        );

        // Supprimer le fichier après l'envoi
        $response->deleteFileAfterSend(true);

        return $response;
    }
}
