<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\OpeningHour;
use App\Repository\RestaurantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;

class DirectoryController extends AbstractController
{
    public function __construct(
        private readonly RestaurantRepository $restaurantRepository
    ) {
    }

    #[Route('/annuaire', name: 'app_directory_index', methods: ['GET'])]
    public function index(): Response
    {
        $restaurants = $this->restaurantRepository->findAllForDirectory();
        $cities = $this->restaurantRepository->findAllCities();
        $departements = $this->restaurantRepository->findAllDepartements();

        return $this->render('directory/index.html.twig', [
            'restaurants' => $restaurants,
            'cities' => $cities,
            'departements' => $departements,
            'currentFilter' => 'all',
            'currentValue' => '',
            'pageTitle' => 'Annuaire des restaurants',
        ]);
    }

    #[Route('/annuaire/ville/{citySlug}', name: 'app_directory_city', methods: ['GET'])]
    public function byCity(string $citySlug): Response
    {
        $city = str_replace('-', ' ', $citySlug);
        $restaurants = $this->restaurantRepository->findByCity($city);

        if (empty($restaurants)) {
            throw $this->createNotFoundException('Aucun restaurant trouvé dans cette ville');
        }

        $cityName = $restaurants[0]->getCity();
        $cities = $this->restaurantRepository->findAllCities();
        $departements = $this->restaurantRepository->findAllDepartements();

        return $this->render('directory/index.html.twig', [
            'restaurants' => $restaurants,
            'cities' => $cities,
            'departements' => $departements,
            'currentFilter' => 'city',
            'currentValue' => $cityName,
            'pageTitle' => 'Restaurants à ' . $cityName,
        ]);
    }

    #[Route('/annuaire/region/{departementSlug}', name: 'app_directory_region', methods: ['GET'])]
    public function byRegion(string $departementSlug): Response
    {
        $departement = str_replace('-', ' ', $departementSlug);
        $restaurants = $this->restaurantRepository->findByDepartement($departement);

        if (empty($restaurants)) {
            throw $this->createNotFoundException('Aucun restaurant trouvé dans ce département');
        }

        $departementName = $restaurants[0]->getDepartement();
        $cities = $this->restaurantRepository->findAllCities();
        $departements = $this->restaurantRepository->findAllDepartements();

        return $this->render('directory/index.html.twig', [
            'restaurants' => $restaurants,
            'cities' => $cities,
            'departements' => $departements,
            'currentFilter' => 'region',
            'currentValue' => $departementName,
            'pageTitle' => 'Restaurants en ' . $departementName,
        ]);
    }

    #[Route('/restaurant/{slug}', name: 'app_directory_restaurant_show', methods: ['GET'])]
    public function show(string $slug): Response
    {
        $restaurant = $this->restaurantRepository->findOneBySlug($slug);

        if (!$restaurant) {
            throw $this->createNotFoundException('Restaurant non trouvé');
        }

        $publishedCartes = $restaurant->getCartes()->filter(
            fn($carte) => $carte->isPublished()
        );

        $activeArdoises = $restaurant->getArdoises()->filter(
            fn($ardoise) => $ardoise->getStatus()
        );

        $openingHoursByDay = [];
        foreach ($restaurant->getOpeningHours() as $oh) {
            $day = $oh->getDayOfWeek();
            if (!isset($openingHoursByDay[$day])) {
                $openingHoursByDay[$day] = [];
            }
            $openingHoursByDay[$day][] = $oh;
        }
        ksort($openingHoursByDay);

        return $this->render('directory/show.html.twig', [
            'restaurant' => $restaurant,
            'publishedCartes' => $publishedCartes,
            'activeArdoises' => $activeArdoises,
            'openingHoursByDay' => $openingHoursByDay,
            'dayNames' => OpeningHour::DAYS,
        ]);
    }

    public static function slugify(string $text): string
    {
        $slugger = new AsciiSlugger('fr');
        return $slugger->slug($text)->lower()->toString();
    }
}
