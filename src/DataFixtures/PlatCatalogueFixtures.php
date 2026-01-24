<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\PlatCatalogue;
use App\Entity\PlatCategorie;
use App\Entity\PlatVariant;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PlatCatalogueFixtures extends Fixture
{
    private const DISHES = [
        // Entrées
        [
            'name' => 'Salade César',
            'description' => 'Salade romaine, poulet grillé, copeaux de parmesan, croûtons maison et sauce César',
            'category' => 'Entrées',
            'tags' => [],
            'variants' => [
                ['label' => 'Normale', 'price' => 890, 'isDefault' => true],
                ['label' => 'Grande', 'price' => 1290, 'isDefault' => false],
                ['label' => 'Sans poulet (Végétarien)', 'price' => 690, 'isDefault' => false],
            ],
        ],
        [
            'name' => 'Foie gras mi-cuit',
            'description' => 'Foie gras de canard maison, chutney de figues, pain de campagne toasté',
            'category' => 'Entrées',
            'tags' => ['Fait maison', 'Allergène: Œufs'],
            'variants' => [
                ['label' => '100g', 'price' => 1490, 'isDefault' => true],
                ['label' => '150g', 'price' => 2190, 'isDefault' => false],
            ],
        ],
        [
            'name' => 'Velouté de butternut',
            'description' => 'Velouté crémeux de courge butternut, crème fouettée et noisettes torréfiées',
            'category' => 'Soupes & Potages',
            'tags' => ['Végétarien', 'Sans gluten', 'Fait maison'],
            'variants' => [
                ['label' => 'Bol 25cl', 'price' => 590, 'isDefault' => true],
                ['label' => 'Grande portion 40cl', 'price' => 890, 'isDefault' => false],
            ],
        ],
        [
            'name' => 'Tartare de saumon',
            'description' => 'Saumon frais coupé au couteau, avocat, citron vert, ciboulette',
            'category' => 'Entrées',
            'tags' => ['Allergène: Poisson', 'Sans gluten'],
            'variants' => [
                ['label' => 'Classique', 'price' => 1190, 'isDefault' => true],
                ['label' => 'Portion généreuse', 'price' => 1690, 'isDefault' => false],
            ],
        ],

        // Plats principaux
        [
            'name' => 'Pavé de bœuf',
            'description' => 'Pavé de bœuf charolais 250g, pommes grenailles rôties, légumes de saison',
            'category' => 'Viandes',
            'tags' => ['Sans gluten', 'Allergène: Lait'],
            'variants' => [
                ['label' => 'Cuisson au choix', 'price' => 2490, 'isDefault' => true],
                ['label' => 'Avec sauce au poivre', 'price' => 2690, 'isDefault' => false],
                ['label' => 'Avec sauce béarnaise', 'price' => 2690, 'isDefault' => false],
            ],
        ],
        [
            'name' => 'Magret de canard',
            'description' => 'Magret de canard rôti, purée de patates douces, sauce aux fruits rouges',
            'category' => 'Viandes',
            'tags' => ['Fait maison', 'Sans gluten'],
            'variants' => [
                ['label' => 'Portion classique', 'price' => 1990, 'isDefault' => true],
                ['label' => 'Double magret', 'price' => 2890, 'isDefault' => false],
            ],
        ],
        [
            'name' => 'Risotto aux champignons',
            'description' => 'Risotto crémeux aux cèpes et champignons de Paris, parmesan, truffe',
            'category' => 'Pâtes & Risottos',
            'tags' => ['Végétarien', 'Fait maison', 'Allergène: Lait'],
            'variants' => [
                ['label' => 'Portion normale', 'price' => 1490, 'isDefault' => true],
                ['label' => 'Grande portion', 'price' => 1890, 'isDefault' => false],
                ['label' => 'Vegan (sans fromage)', 'price' => 1390, 'isDefault' => false],
            ],
        ],
        [
            'name' => 'Dos de cabillaud',
            'description' => 'Dos de cabillaud rôti, écrasé de pommes de terre à l\'huile d\'olive, beurre blanc',
            'category' => 'Poissons & Fruits de mer',
            'tags' => ['Allergène: Poisson', 'Allergène: Lait'],
            'variants' => [
                ['label' => 'Portion 180g', 'price' => 1890, 'isDefault' => true],
                ['label' => 'Portion XL 250g', 'price' => 2390, 'isDefault' => false],
            ],
        ],
        [
            'name' => 'Curry de légumes',
            'description' => 'Curry de légumes de saison au lait de coco, riz basmati, naan maison',
            'category' => 'Plats principaux',
            'tags' => ['Vegan', 'Végétalien', 'Sans gluten', 'Épicé', 'Fait maison'],
            'variants' => [
                ['label' => 'Épicé moyen', 'price' => 1290, 'isDefault' => true],
                ['label' => 'Très épicé', 'price' => 1290, 'isDefault' => false],
                ['label' => 'Doux', 'price' => 1290, 'isDefault' => false],
            ],
        ],
        [
            'name' => 'Pizza Margherita',
            'description' => 'Tomate San Marzano, mozzarella di bufala, basilic frais, huile d\'olive',
            'category' => 'Plats principaux',
            'tags' => ['Végétarien', 'Fait maison', 'Allergène: Lait'],
            'variants' => [
                ['label' => 'Classique', 'price' => 1190, 'isDefault' => true],
                ['label' => 'XXL', 'price' => 1690, 'isDefault' => false],
                ['label' => 'À emporter', 'price' => 1090, 'isDefault' => false],
            ],
        ],
        [
            'name' => 'Burger maison',
            'description' => 'Pain brioché, steak haché 180g, cheddar, bacon, tomate, salade, sauce maison, frites',
            'category' => 'Plats principaux',
            'tags' => ['Fait maison', 'Allergène: Lait', 'Allergène: Œufs'],
            'variants' => [
                ['label' => 'Simple', 'price' => 1590, 'isDefault' => true],
                ['label' => 'Double steak', 'price' => 1990, 'isDefault' => false],
                ['label' => 'Végétarien (galette légumes)', 'price' => 1490, 'isDefault' => false],
            ],
        ],

        // Desserts
        [
            'name' => 'Tarte Tatin',
            'description' => 'Tarte aux pommes caramélisées, glace vanille bourbon',
            'category' => 'Desserts',
            'tags' => ['Fait maison', 'Allergène: Lait', 'Allergène: Œufs'],
            'variants' => [
                ['label' => 'Part individuelle', 'price' => 790, 'isDefault' => true],
                ['label' => 'Sans glace', 'price' => 690, 'isDefault' => false],
            ],
        ],
        [
            'name' => 'Mousse au chocolat',
            'description' => 'Mousse au chocolat noir 70% de cacao, chantilly maison',
            'category' => 'Desserts',
            'tags' => ['Fait maison', 'Sans gluten', 'Végétarien', 'Allergène: Œufs', 'Allergène: Lait'],
            'variants' => [
                ['label' => 'Portion normale', 'price' => 690, 'isDefault' => true],
                ['label' => 'Grande portion', 'price' => 890, 'isDefault' => false],
                ['label' => 'Vegan (chocolat + aquafaba)', 'price' => 790, 'isDefault' => false],
            ],
        ],
        [
            'name' => 'Crème brûlée',
            'description' => 'Crème brûlée à la vanille de Madagascar, caramélisée minute',
            'category' => 'Desserts',
            'tags' => ['Fait maison', 'Sans gluten', 'Allergène: Lait', 'Allergène: Œufs'],
            'variants' => [
                ['label' => 'Vanille', 'price' => 690, 'isDefault' => true],
                ['label' => 'Café', 'price' => 690, 'isDefault' => false],
                ['label' => 'Pistache', 'price' => 790, 'isDefault' => false],
            ],
        ],
        [
            'name' => 'Salade de fruits frais',
            'description' => 'Assortiment de fruits de saison, menthe fraîche, sirop de citron',
            'category' => 'Desserts',
            'tags' => ['Vegan', 'Végétalien', 'Sans gluten', 'Sans lactose', 'Bio'],
            'variants' => [
                ['label' => 'Coupe', 'price' => 590, 'isDefault' => true],
                ['label' => 'Grande coupe', 'price' => 790, 'isDefault' => false],
            ],
        ],

        // Fromages
        [
            'name' => 'Plateau de fromages',
            'description' => 'Sélection de 5 fromages fermiers, confiture de figues, pain aux noix',
            'category' => 'Fromages',
            'tags' => ['Allergène: Lait'],
            'variants' => [
                ['label' => 'Pour 1 personne', 'price' => 990, 'isDefault' => true],
                ['label' => 'Pour 2 personnes', 'price' => 1690, 'isDefault' => false],
            ],
        ],

        // Boissons
        [
            'name' => 'Jus de fruits frais pressés',
            'description' => 'Jus pressé minute',
            'category' => 'Boissons',
            'tags' => ['Vegan', 'Sans gluten', 'Bio'],
            'variants' => [
                ['label' => 'Orange', 'price' => 490, 'isDefault' => true],
                ['label' => 'Pamplemousse', 'price' => 490, 'isDefault' => false],
                ['label' => 'Pomme-Gingembre', 'price' => 590, 'isDefault' => false],
                ['label' => 'Carotte-Orange', 'price' => 590, 'isDefault' => false],
            ],
        ],
        [
            'name' => 'Café',
            'description' => 'Café arabica torréfié maison',
            'category' => 'Cafés & Thés',
            'tags' => ['Vegan', 'Sans gluten', 'Bio'],
            'variants' => [
                ['label' => 'Espresso', 'price' => 290, 'isDefault' => true],
                ['label' => 'Allongé', 'price' => 290, 'isDefault' => false],
                ['label' => 'Noisette', 'price' => 320, 'isDefault' => false],
                ['label' => 'Cappuccino', 'price' => 390, 'isDefault' => false],
            ],
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        // Get the first user as owner
        $user = $manager->getRepository(User::class)->findOneBy([]);

        if (!$user) {
            throw new \RuntimeException('No user found in database. Please create a user first.');
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
            $dish = new PlatCatalogue();
            $dish->setName($dishData['name']);
            $dish->setDescription($dishData['description']);
            $dish->setOwner($user);

            // Set category
            if (isset($categories[$dishData['category']])) {
                $dish->setCategory($categories[$dishData['category']]);
            }

            // Add tags
            foreach ($dishData['tags'] as $tagLabel) {
                if (isset($tags[$tagLabel])) {
                    $dish->addTag($tags[$tagLabel]);
                }
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
