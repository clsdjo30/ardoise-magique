<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\OpeningHour;
use App\Entity\PlatCatalogue;
use App\Entity\PlatCategorie;
use App\Entity\PlatVariant;
use App\Entity\Restaurant;
use App\Entity\Subscription;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class BistrotLePetitJardinFixtures extends Fixture
{
    private const USER_EMAIL = 'petitjardin@example.com';
    private const USER_PASSWORD = 'jardin123';
    private const USER_FIRSTNAME = 'Jardin';
    private const RESTAURANT_NAME = 'Le Petit Jardin';
    private const DISHES = [
        [
                'name' => 'Abricothym',
                'description' => 'Gin thym, Apricot nectar, ginger syrup, lemon juice',
                'category' => 'Cocktail',
                'variants' => [
                    ['label' => 'Verre', 'price' => 1600, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'Campari des Îles',
                'description' => 'Brown rum, honey, campari, pineapple juice',
                'category' => 'Cocktail',
                'variants' => [
                    ['label' => 'Verre', 'price' => 1600, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'Rouge d’Été',
                'description' => 'Vodka, verbena infusion, raspberry purée, lime juice',
                'category' => 'Cocktail',
                'variants' => [
                    ['label' => 'Verre', 'price' => 1600, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'La Madeleine',
                'description' => 'Amaretto, pineapple juice, lemon juice',
                'category' => 'Cocktail',
                'variants' => [
                    ['label' => 'Verre', 'price' => 1600, 'isDefault' => true]
                ],
            ],

        // Tapas et Entrées
            [
                'name' => 'Beetroot and pasteque gazpacho',
                'description' => 'Feta and mint oil',
                'category' => 'Tapas & Starters',
                'variants' => [
                    ['label' => 'Portion', 'price' => 1100, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'Sea bream and gilthead bream gravlax',
                'description' => 'With passion fruit garnish',
                'category' => 'Tapas & Starters',
                'variants' => [
                    ['label' => 'Portion', 'price' => 1500, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'Cod accras',
                'description' => 'With Thai peanut sauce and crunchy vegetables',
                'category' => 'Tapas & Starters',
                'variants' => [
                    ['label' => 'Portion', 'price' => 1500, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'Falafels on grilled peppers',
                'description' => 'Mint and lemon cream',
                'category' => 'Tapas & Starters',
                'variants' => [
                    ['label' => 'Portion', 'price' => 1500, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'Chicken brochette',
                'description' => 'With ginger, Yakitori sauce and salad',
                'category' => 'Tapas & Starters',
                'variants' => [
                    ['label' => 'Brochette', 'price' => 1300, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'Beef tongue "à la chilienne"',
                'description' => 'With "Pebre" condiment, crunchy vegetables',
                'category' => 'Tapas & Starters',
                'variants' => [
                    ['label' => 'Portion', 'price' => 1200, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'Arepas of pulled pork',
                'description' => 'Mozzarella and red onion pickles',
                'category' => 'Tapas & Starters',
                'variants' => [
                    ['label' => 'Portion', 'price' => 1100, 'isDefault' => true]
                ],
            ],
            // Main courses
            [
                'name' => 'Linguines "Alle vongole"',
                'description' => '',
                'category' => 'Main Courses',
                'variants' => [
                    ['label' => 'Portion', 'price' => 2200, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'Skate wings with Grenoble sauce',
                'description' => 'Spinach risotto',
                'category' => 'Main Courses',
                'variants' => [
                    ['label' => 'Portion', 'price' => 2300, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'Grilled octopus focaccia',
                'description' => 'Served with lamb\'s lettuce salad',
                'category' => 'Main Courses',
                'variants' => [
                    ['label' => 'Portion', 'price' => 2100, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'Céviche of lean meat',
                'description' => 'Grilled corn and broccoli',
                'category' => 'Main Courses',
                'variants' => [
                    ['label' => 'Portion', 'price' => 2200, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'Tataki de bœuf mariné au soja',
                'description' => 'Salad of Chinese cabbage, fries',
                'category' => 'Main Courses',
                'variants' => [
                    ['label' => 'Portion', 'price' => 2400, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'Tajine of beef kefta',
                'description' => 'With vegetables, Moroccan "Batbout" bread',
                'category' => 'Main Courses',
                'variants' => [
                    ['label' => 'Portion', 'price' => 2200, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'Marinated lamb shoulder',
                'description' => 'Served whole, for two people, two choices of garnish',
                'category' => 'Main Courses',
                'variants' => [
                    ['label' => 'Portion', 'price' => 3200, 'isDefault' => true]
                ],
            ],
            // Additional side dishes
            [
                'name' => 'Homemade French fries',
                'description' => '',
                'category' => 'Side Dishes',
                'variants' => [
                    ['label' => 'Side', 'price' => 500, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'Grilled corn and broccoli',
                'description' => '',
                'category' => 'Side Dishes',
                'variants' => [
                    ['label' => 'Side', 'price' => 500, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'Creamy risotto with pepper coulis',
                'description' => '',
                'category' => 'Side Dishes',
                'variants' => [
                    ['label' => 'Side', 'price' => 600, 'isDefault' => true]
                ],
            ],
            // Desserts
            [
                'name' => 'Cheese Platter',
                'description' => '',
                'category' => 'Desserts',
                'variants' => [
                    ['label' => 'Platter', 'price' => 1200, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'Paris-Brest',
                'description' => 'The Petit Jardin\'s version of the classic Paris-Brest',
                'category' => 'Desserts',
                'variants' => [
                    ['label' => 'Portion', 'price' => 1100, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'Finger royal dark chocolate',
                'description' => 'Two-chocolate ganache',
                'category' => 'Desserts',
                'variants' => [
                    ['label' => 'Portion', 'price' => 1100, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'Pavlova with seasonal fruits',
                'description' => '',
                'category' => 'Desserts',
                'variants' => [
                    ['label' => 'Portion', 'price' => 1100, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'Coffee & dessert selection',
                'description' => '',
                'category' => 'Desserts',
                'variants' => [
                    ['label' => 'Set', 'price' => 1200, 'isDefault' => true]
                ],
            ],
    ];

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $user = $manager->getRepository(User::class)->findOneBy(['email' => self::USER_EMAIL]);
        if (!$user) {
            $user = new User();
            $user->setEmail(self::USER_EMAIL);
            $user->setFirstname(self::USER_FIRSTNAME);
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, self::USER_PASSWORD)
            );
            $manager->persist($user);
        }

        $subscription = $user->getSubscription();
        if (!$subscription) {
            $subscription = new Subscription();
            $subscription->setUser($user);
            $subscription->setPlanCode('PREMIUM');
            $subscription->setStatus('active');
            $manager->persist($subscription);
        }

        $restaurant = $manager->getRepository(Restaurant::class)->findOneBy([
            'name' => self::RESTAURANT_NAME,
            'owner' => $user,
        ]);

        if (!$restaurant) {
            $restaurant = new Restaurant();
            $restaurant->setName(self::RESTAURANT_NAME);
            $restaurant->setAddress('5 avenue de Verdun');
            $restaurant->setZipCode('06000');
            $restaurant->setCity('Nice');
            $restaurant->setDepartement('Alpes-Maritimes');
            $restaurant->setPhoneNumber('0493870000');
            $restaurant->setFacebookPage('https://facebook.com/lepetitjardinnice');
            $restaurant->setOwner($user);
            $manager->persist($restaurant);
        }

        if ($restaurant->getOpeningHours()->isEmpty()) {
            $openingHours = [
                ['day' => 2, 'opens' => '12:00', 'closes' => '14:30', 'label' => 'Midi'],
                ['day' => 2, 'opens' => '19:00', 'closes' => '22:30', 'label' => 'Soir'],
                ['day' => 3, 'opens' => '12:00', 'closes' => '14:30', 'label' => 'Midi'],
                ['day' => 3, 'opens' => '19:00', 'closes' => '22:30', 'label' => 'Soir'],
                ['day' => 4, 'opens' => '12:00', 'closes' => '14:30', 'label' => 'Midi'],
                ['day' => 4, 'opens' => '19:00', 'closes' => '22:30', 'label' => 'Soir'],
                ['day' => 5, 'opens' => '12:00', 'closes' => '14:30', 'label' => 'Midi'],
                ['day' => 5, 'opens' => '19:00', 'closes' => '22:30', 'label' => 'Soir'],
                ['day' => 6, 'opens' => '12:00', 'closes' => '15:00', 'label' => 'Midi'],
                ['day' => 6, 'opens' => '19:00', 'closes' => '23:00', 'label' => 'Soir'],
                ['day' => 7, 'opens' => '12:00', 'closes' => '15:00', 'label' => 'Midi'],
            ];

            foreach ($openingHours as $data) {
                $openingHour = new OpeningHour();
                $openingHour->setRestaurant($restaurant);
                $openingHour->setDayOfWeek($data['day']);
                $openingHour->setOpensAt(new \DateTimeImmutable($data['opens']));
                $openingHour->setClosesAt(new \DateTimeImmutable($data['closes']));
                $openingHour->setLabel($data['label']);
                $manager->persist($openingHour);
            }
        }

        // Get all categories indexed by titre
        $categories = [];
        foreach ($manager->getRepository(PlatCategorie::class)->findAll() as $category) {
            $categories[$category->getTitre()] = $category;
        }

        // Get all tags indexed by label
        $tags = [];
        foreach ($manager->getRepository(Tag::class)->findAll() as $tag) {
            $tags[$tag->getLabel()] = $tag;
        }

        $dishCount = 0;
        $variantCount = 0;

        foreach (self::DISHES as $dishData) {
            $existingDish = $manager->getRepository(PlatCatalogue::class)->findOneBy([
                'name' => $dishData['name'],
                'owner' => $user,
            ]);

            if ($existingDish) {
                continue;
            }

            $dish = new PlatCatalogue();
            $dish->setName($dishData['name']);
            $dish->setDescription($dishData['description']);
            $dish->setOwner($user);

            // Set category
            if (!empty($dishData['category'])) {
                if (!isset($categories[$dishData['category']])) {
                    $category = new PlatCategorie();
                    $category->setTitre($dishData['category']);
                    $manager->persist($category);
                    $categories[$dishData['category']] = $category;
                }
                $dish->setCategory($categories[$dishData['category']]);
            }

            // Add tags
            foreach ($dishData['tags'] ?? [] as $tagLabel) {
                if (!isset($tags[$tagLabel])) {
                    $tag = new Tag();
                    $tag->setLabel($tagLabel);
                    $manager->persist($tag);
                    $tags[$tagLabel] = $tag;
                }
                $dish->addTag($tags[$tagLabel]);
            }

            // Create variants
            foreach ($dishData['variants'] as $variantData) {
                $variant = new PlatVariant();
                $variant->setLabel($variantData['label']);
                $variant->setPriceCents($variantData['price']);
                $variant->setTvaRate('10.0'); // TVA restaurant 10%
                $variant->setIsDefault($variantData['isDefault']);
                $variant->setPlatCatalogue($dish);

                $dish->addVariant($variant);
                $manager->persist($variant);
                $variantCount++;
            }

            $manager->persist($dish);
            $dishCount++;
        }

        $manager->flush();

        echo sprintf("\n✅ Created %d dishes with %d variants\n", $dishCount, $variantCount);
    }
}
