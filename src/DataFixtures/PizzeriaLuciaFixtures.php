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

class PizzeriaLuciaFixtures extends Fixture
{
    private const USER_EMAIL = 'lucia@example.com';
    private const USER_PASSWORD = 'lucia123';
    private const USER_FIRSTNAME = 'Lucia';
    private const RESTAURANT_NAME = 'La Pizzeria Lucia';
    private const DISHES = [
       // Boissons
            [
                'name' => 'Martini rouge ou Rosée ou Blanc',
                'description' => '19 cl',
                'category' => 'Apéritif',
                'variants' => [
                    ['label' => '19 cl', 'price' => 600, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'Limoncello',
                'description' => '19 cl',
                'category' => 'Apéritif',
                'variants' => [
                    ['label' => '19 cl', 'price' => 480, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'Bruschetta',
                'description' => 'Apéritif',
                'category' => 'Apéritif',
                'variants' => [
                    ['label' => 'Portion', 'price' => 550, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'Côte du Rhône AOP',
                'description' => '19 cl, 50 cl, 75 cl',
                'category' => 'Vin',
                'variants' => [
                    ['label' => '19 cl', 'price' => 410, 'isDefault' => true],
                    ['label' => '50 cl', 'price' => 900, 'isDefault' => false],
                    ['label' => '75 cl', 'price' => 1400, 'isDefault' => false]
                ],
            ],
            [
                'name' => 'Rosé Ardèche IGP',
                'description' => '19 cl, 50 cl, 75 cl',
                'category' => 'Vin',
                'variants' => [
                    ['label' => '19 cl', 'price' => 410, 'isDefault' => true],
                    ['label' => '50 cl', 'price' => 950, 'isDefault' => false],
                    ['label' => '75 cl', 'price' => 1550, 'isDefault' => false]
                ],
            ],
            [
                'name' => 'Blanc Viognier AOP',
                'description' => '19 cl, 50 cl, 75 cl',
                'category' => 'Vin',
                'variants' => [
                    ['label' => '19 cl', 'price' => 410, 'isDefault' => true],
                    ['label' => '50 cl', 'price' => 950, 'isDefault' => false],
                    ['label' => '75 cl', 'price' => 1550, 'isDefault' => false]
                ],
            ],
            [
                'name' => 'Peroni',
                'description' => '33 cl',
                'category' => 'Bière',
                'variants' => [
                    ['label' => '33 cl', 'price' => 420, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'Heineken',
                'description' => '33 cl',
                'category' => 'Bière',
                'variants' => [
                    ['label' => '33 cl', 'price' => 390, 'isDefault' => true]
                ],
            ],
            [
                'name' => '1664',
                'description' => '33 cl',
                'category' => 'Bière',
                'variants' => [
                    ['label' => '33 cl', 'price' => 390, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'Desperados',
                'description' => '33 cl',
                'category' => 'Bière',
                'variants' => [
                    ['label' => '33 cl', 'price' => 480, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'Coca',
                'description' => '50 cl',
                'category' => 'Soda',
                'variants' => [
                    ['label' => '50 cl', 'price' => 290, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'Zéro',
                'description' => '50 cl',
                'category' => 'Soda',
                'variants' => [
                    ['label' => '50 cl', 'price' => 290, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'Cherry',
                'description' => '50 cl',
                'category' => 'Soda',
                'variants' => [
                    ['label' => '50 cl', 'price' => 290, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'Orangina',
                'description' => '50 cl',
                'category' => 'Soda',
                'variants' => [
                    ['label' => '50 cl', 'price' => 290, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'Lipton',
                'description' => '50 cl',
                'category' => 'Soda',
                'variants' => [
                    ['label' => '50 cl', 'price' => 290, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'Volvic fraise',
                'description' => '50 cl',
                'category' => 'Soda',
                'variants' => [
                    ['label' => '50 cl', 'price' => 290, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'Fanta orange',
                'description' => '50 cl',
                'category' => 'Soda',
                'variants' => [
                    ['label' => '50 cl', 'price' => 290, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'Cristaline',
                'description' => '50 cl',
                'category' => 'Eau',
                'variants' => [
                    ['label' => '50 cl', 'price' => 150, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'San Pellegrino',
                'description' => '50 cl',
                'category' => 'Eau',
                'variants' => [
                    ['label' => '50 cl', 'price' => 270, 'isDefault' => true]
                ],
            ],
        // Pizzas
          [
                'name' => 'La Classica',
                'description' => 'Tomate, fromage, olives noires',
                'category' => 'Base Tomate',
                'variants' => [
                    ['label' => 'Pizza individuelle', 'price' => 1100, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'La Vera',
                'description' => 'Tomate, mozzarella fraîche, olives noires',
                'category' => 'Base Tomate',
                'variants' => [
                    ['label' => 'Pizza individuelle', 'price' => 1150, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'La Bella Verde',
                'description' => 'Tomate, fromage, mozzarella fraîche, pesto, olives noires',
                'category' => 'Base Tomate',
                'variants' => [
                    ['label' => 'Pizza individuelle', 'price' => 1200, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'La Giadiniera',
                'description' => 'Tomate, fromage, champignons, poivrons, oignons rouges, courgettes, pesto',
                'category' => 'Base Tomate',
                'variants' => [
                    ['label' => 'Pizza individuelle', 'price' => 1200, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'La Fresca',
                'description' => 'Tomate, fromage, tomates cerises, roquette, olives noires',
                'category' => 'Base Tomate',
                'variants' => [
                    ['label' => 'Pizza individuelle', 'price' => 1200, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'La Capriciosa',
                'description' => 'Tomate, fromage, champignons, fonds d’artichauts, œuf, olives noires',
                'category' => 'Base Tomate',
                'variants' => [
                    ['label' => 'Pizza individuelle', 'price' => 1250, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'La Bolognese',
                'description' => 'Tomate, fromage, viandes hachées pure bœuf, olives noires',
                'category' => 'Base Tomate',
                'variants' => [
                    ['label' => 'Pizza individuelle', 'price' => 1250, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'La Diavola',
                'description' => 'Tomate, fromage, spianata (chorizo italien piquant), parmesan, olives noires',
                'category' => 'Base Tomate',
                'variants' => [
                    ['label' => 'Pizza individuelle', 'price' => 1250, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'La Regina',
                'description' => 'Tomate, fromage, champignons, jambon blanc, œuf, olives noires',
                'category' => 'Base Tomate',
                'variants' => [
                    ['label' => 'Pizza individuelle', 'price' => 1250, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'L\'Orientale',
                'description' => 'Tomate, fromage, chorizo, merguez, poivrons, olives noires',
                'category' => 'Base Tomate',
                'variants' => [
                    ['label' => 'Pizza individuelle', 'price' => 1300, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'La Sicilienne',
                'description' => 'Tomate, fromage, anchois, câpres ou caprons, olives noires',
                'category' => 'Base Tomate',
                'variants' => [
                    ['label' => 'Pizza individuelle', 'price' => 1300, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'La Quattro Fromaggi',
                'description' => 'Tomate, fromage mozzarella, bleu, reblochon, chèvre, olives noires',
                'category' => 'Base Tomate',
                'variants' => [
                    ['label' => 'Pizza individuelle', 'price' => 1350, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'La Serrano',
                'description' => 'Tomate, fromage, jambon de Serrano, olives noires',
                'category' => 'Base Tomate',
                'variants' => [
                    ['label' => 'Pizza individuelle', 'price' => 1350, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'La Gustoso',
                'description' => 'Tomate, fromage, tomates cerises, véritable burrata, jambon de Parme, olives noires',
                'category' => 'Base Tomate',
                'variants' => [
                    ['label' => 'Pizza individuelle', 'price' => 1400, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'La Calzone',
                'description' => 'Tomate, fromage, champignons, jambon blanc, œuf',
                'category' => 'Base Tomate',
                'variants' => [
                    ['label' => 'Pizza individuelle', 'price' => 1400, 'isDefault' => true]
                ],
            ],
            // Pizzas Base Crème
            [
                'name' => 'La Capra Miela',
                'description' => 'Crème fraîche, fromage, fromage de chèvre, miel',
                'category' => 'Base Crème',
                'variants' => [
                    ['label' => 'Pizza individuelle', 'price' => 1250, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'La Greca',
                'description' => 'Yaourt grec, fromage, Feta, poivrons rouges, olives noires',
                'category' => 'Base Crème',
                'variants' => [
                    ['label' => 'Pizza individuelle', 'price' => 1250, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'La Polo Goloso',
                'description' => 'Crème fraîche, fromage, poulet, oignons rouges, olives noires',
                'category' => 'Base Crème',
                'variants' => [
                    ['label' => 'Pizza individuelle', 'price' => 1300, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'La Carbonara',
                'description' => 'Crème fraîche, fromage, lardons, œuf',
                'category' => 'Base Crème',
                'variants' => [
                    ['label' => 'Pizza individuelle', 'price' => 1300, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'La Toribovino',
                'description' => 'Crème fraîche moutarde à l\'ancienne, fromage, viande hachée pure bœuf, poulet, oignons rouges',
                'category' => 'Base Crème',
                'variants' => [
                    ['label' => 'Pizza individuelle', 'price' => 1350, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'La Vitello Spezie',
                'description' => 'Crème fraîche, fromage, veau, oignons rouges, tomates cerises, olives noires',
                'category' => 'Base Crème',
                'variants' => [
                    ['label' => 'Pizza individuelle', 'price' => 1350, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'La Marina',
                'description' => 'Crème fraîche, fromage, thon, anchois, tomates cerises, olives noires',
                'category' => 'Base Crème',
                'variants' => [
                    ['label' => 'Pizza individuelle', 'price' => 1350, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'La Montanara',
                'description' => 'Crème fraîche, fromage, champignons, Saint-Nectaire, Reblochon, lardons, oignons rouges',
                'category' => 'Base Crème',
                'variants' => [
                    ['label' => 'Pizza individuelle', 'price' => 1350, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'La Savoarda',
                'description' => 'Crème fraîche, pommes de terre, fromage à raclette, Reblochon, oignons rouges, lardons',
                'category' => 'Base Crème',
                'variants' => [
                    ['label' => 'Pizza individuelle', 'price' => 1350, 'isDefault' => true]
                ],
            ],
            [
                'name' => 'La Salmone',
                'description' => 'Crème fraîche, fromage, saumon frais, saumon fumé, tomates cerises, olives noires',
                'category' => 'Base Crème',
                'variants' => [
                    ['label' => 'Pizza individuelle', 'price' => 1400, 'isDefault' => true]
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
            $user->setRoles(['ROLE_USER', 'ROLE_RESTAURANT_OWNER']);
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
            $restaurant->setAddress('12 rue Felix Viallet');
            $restaurant->setZipCode('38000');
            $restaurant->setCity('Grenoble');
            $restaurant->setDepartement('Isere');
            $restaurant->setPhoneNumber('0476420000');
            $restaurant->setFacebookPage('https://facebook.com/pizzerialucia');
            $restaurant->setOwner($user);
            $manager->persist($restaurant);
        }

        if ($restaurant->getOpeningHours()->isEmpty()) {
            $openingHours = [
                ['day' => 2, 'opens' => '11:30', 'closes' => '14:00', 'label' => 'Midi'],
                ['day' => 2, 'opens' => '18:30', 'closes' => '22:00', 'label' => 'Soir'],
                ['day' => 3, 'opens' => '11:30', 'closes' => '14:00', 'label' => 'Midi'],
                ['day' => 3, 'opens' => '18:30', 'closes' => '22:00', 'label' => 'Soir'],
                ['day' => 4, 'opens' => '11:30', 'closes' => '14:00', 'label' => 'Midi'],
                ['day' => 4, 'opens' => '18:30', 'closes' => '22:00', 'label' => 'Soir'],
                ['day' => 5, 'opens' => '11:30', 'closes' => '14:00', 'label' => 'Midi'],
                ['day' => 5, 'opens' => '18:30', 'closes' => '22:30', 'label' => 'Soir'],
                ['day' => 6, 'opens' => '11:30', 'closes' => '14:30', 'label' => 'Midi'],
                ['day' => 6, 'opens' => '18:30', 'closes' => '23:00', 'label' => 'Soir'],
                ['day' => 7, 'opens' => '18:30', 'closes' => '22:00', 'label' => 'Soir'],
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
