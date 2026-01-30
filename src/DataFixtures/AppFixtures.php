<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $now = new \DateTimeImmutable();

        /** =========================
         *  PRODUITS
         *  ========================= */
        $products = [
            [
                'name' => 'Bougie Lavande & Patchouli',
                'shortDescription' => 'Bougie parfumée en cire naturelle avec bouchon en liège',
                'fullDescription' => 'Cette bougie associe la douceur florale de la lavande à la profondeur boisée du patchouli pour créer une atmosphère apaisante et chaleureuse. Présentée dans un pot en verre élégant, elle est fermée par un bouchon en liège naturel qui protège la cire et conserve le parfum. Idéale pour les moments de détente.',
                'price' => 2990,
                'image' => 'assets/img/products/candles.png',
            ],
            [
                'name' => 'Set de couverts nomades en bois',
                'shortDescription' => 'Couverts réutilisables avec pochette en tissu',
                'fullDescription' => 'Ce set comprend une fourchette, un couteau et une cuillère en bois, rangés dans une pochette en tissu naturel. Parfait pour les repas à emporter, il permet de réduire l’utilisation de couverts jetables au quotidien.',
                'price' => 1190,
                'image' => 'assets/img/products/cutlery.png',
            ],
            [
                'name' => 'Déodorant bio à l’eucalyptus',
                'shortDescription' => 'Déodorant naturel frais et efficace – 50 ml',
                'fullDescription' => 'Ce déodorant naturel à l’eucalyptus procure une sensation de fraîcheur durable. Sa formule minimaliste respecte la peau tout en limitant les odeurs. Son format compact est idéal pour une utilisation quotidienne.',
                'price' => 890,
                'image' => 'assets/img/products/deo.png',
            ],
            [
                'name' => 'Cotons démaquillants lavables',
                'shortDescription' => 'Disques réutilisables doux pour la peau',
                'fullDescription' => 'Ces cotons démaquillants lavables remplacent les disques jetables. Doux et résistants, ils conviennent à tous les types de peau et s’utilisent avec de l’eau, une lotion ou une huile démaquillante.',
                'price' => 1490,
                'image' => 'assets/img/products/disks.png',
            ],
            [
                'name' => 'Kit d’hygiène naturel',
                'shortDescription' => 'Essentiels durables pour la salle de bain',
                'fullDescription' => 'Ce kit d’hygiène regroupe les indispensables du quotidien dans une approche plus responsable. Idéal pour débuter une transition vers une salle de bain zéro déchet ou comme idée cadeau utile.',
                'price' => 2490,
                'image' => 'assets/img/products/hygiene-kit.png',
            ],
            [
                'name' => 'Sacs biodégradables',
                'shortDescription' => 'Alternative responsable aux sacs plastiques',
                'fullDescription' => 'Ces sacs biodégradables sont conçus pour la gestion des déchets domestiques ou organiques. Ils constituent une alternative plus respectueuse de l’environnement aux sacs plastiques classiques.',
                'price' => 690,
                'image' => 'assets/img/products/plastic-bag.png',
            ],
            [
                'name' => 'Savon naturel artisanal',
                'shortDescription' => 'Savon doux à base d’ingrédients naturels',
                'fullDescription' => 'Ce savon artisanal est formulé à partir d’ingrédients d’origine naturelle pour nettoyer la peau en douceur. Adapté aux mains comme au corps, pour un usage quotidien.',
                'price' => 790,
                'image' => 'assets/img/products/soap.png',
            ],
            [
                'name' => 'Brosses à dents en bois',
                'shortDescription' => 'Brosses à dents écologiques pour toute la famille',
                'fullDescription' => 'Ces brosses à dents en bois sont une alternative durable aux modèles en plastique. Leur manche ergonomique assure une bonne prise en main et un brossage efficace.',
                'price' => 990,
                'image' => 'assets/img/products/teeth-brushes.png',
            ],
            [
                'name' => 'Gourde isotherme effet bois',
                'shortDescription' => 'Gourde en inox avec finition bois',
                'fullDescription' => 'Cette gourde isotherme conserve les boissons chaudes ou froides pendant plusieurs heures. Son design effet bois apporte une touche naturelle et élégante.',
                'price' => 1990,
                'image' => 'assets/img/products/wood-bottle.png',
            ],
            [
                'name' => 'Jus pressé à froid Ananas & Mangue',
                'shortDescription' => 'Boisson fruitée sans sucre ajouté',
                'fullDescription' => 'Ce jus pressé à froid associe l’ananas et la mangue pour une boisson naturellement sucrée et rafraîchissante. Idéal pour une pause vitaminée à tout moment de la journée.',
                'price' => 490,
                'image' => 'assets/img/products/yellow-juice.png',
            ],
        ];

        foreach ($products as $data) {
            $product = new Product();
            $product
                ->setName($data['name'])
                ->setShortDescription($data['shortDescription'])
                ->setFullDescription($data['fullDescription'])
                ->setPrice($data['price'])
                ->setImage($data['image'])
                ->setIsPublished(true)
                ->setCreatedAt($now);

            $manager->persist($product);
        }

        // Création des utilisateurs
        $usersData = [
            [
                'email' => 'john@me.com',
                'password' => 'john',
                'roles' => ['ROLE_USER'],
                'apiEnabled' => false,
            ],
            [
                'email' => 'admin@greengoodies.local',
                'password' => 'admin',
                'roles' => ['ROLE_ADMIN'],
                'apiEnabled' => true,
            ],
        ];

        foreach ($usersData as $userData) {
            $user = new User();

            $user
                ->setEmail($userData['email'])
                ->setRoles($userData['roles'])
                ->setApiEnabled($userData['apiEnabled'])
                ->setCreatedAt(new \DateTimeImmutable());

            // Hachage du mot de passe
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $userData['password']
            );
            $user->setPassword($hashedPassword);

            $manager->persist($user);
        }

        $manager->flush();

    }
}
