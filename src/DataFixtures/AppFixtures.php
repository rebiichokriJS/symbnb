<?php

namespace App\DataFixtures;

use App\Entity\Ad;
use Faker\Factory;
use App\Entity\User;
use App\Entity\Image;
use App\Entity\Role;
// use Cocur\Slugify\Slugify;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {

        // on set le charset a faker
        $faker = Factory::create('FR-fr');
        $adminRole = new Role();
        $adminRole->setTitle('ROLE_ADMIN');
        $manager->persist($adminRole);

        $adminUser = new User();
        $adminUser->setFirstName('Benoit')
                ->setLastName('petit')
                ->setEmail('benoit@mail.com')
                ->setHash($this->encoder->encodePassword($adminUser, 'password'))
                ->setPicture('https://avatars.io/twitter/codingben_')
                ->setIntroduction($faker->sentence)
                ->setDescription('<p>' . join('</p><p>', $faker->paragraphs(3)) . '</p>')
                ->addUserRole($adminRole);
        $manager->persist($adminUser);
        $manager->flush();
        // slug gerer avec l'entité !!!!!
        // $slugify = new Slugify();
        $users = [];
        $genres = ['male', 'female'];
        
        // boucle creation de données via fixtures (USER)
        for ($i=0; $i < 10; $i++) { 
            $user = new User();

            $genre = $faker->randomElement($genres);
            $picture = 'https://randomuser.me/api/portraits/';
            $pictureId = $faker->numberBetween(1, 99) . '.jpg';

            $picture .= ($genre == 'male' ? 'men/' : 'women/') . $pictureId;

            $hash = $this->encoder->encodePassword($user, 'password');

            $user->setFirstName($faker->firstName($genre))
                ->setLastName($faker->lastName)
                ->setEmail($faker->email)
                ->setIntroduction($faker->sentence)
                ->setDescription('<p>' . join('</p><p>', $faker->paragraphs(3)) . '</p>')
                ->setHash($hash)
                ->setPicture($picture);

            $manager->persist($user);
            $users[] = $user;
            $manager->flush();
        }


        // boucle creation de données via fixtures (ANNONCE)
        for ($i=1; $i < 30; $i++) { 
             // set des données
            $title = $faker->sentence();
            // $slug = $slugify->slugify($title);
            $cover = $faker->imageUrl(1000,350);
            $introduction = $faker->paragraph(2);
            $content = '<p>' . join('</p><p>', $faker->paragraphs(5)) . '</p>';

            $user = $users[mt_rand(0,count($users) - 1)];
            
            // creation d'une annonce
            $ad = new Ad();
            // set des informations d'annonce avec faker
            $ad->setTitle($title)
            // ->setSlug($slug)
            ->setCoverImage($cover)
            ->setIntroduction($introduction)
            ->setContent($content)
            ->setPrice(mt_rand(40,200))
            ->setRooms(mt_rand(1,5))
            ->setAuthor($user);

            // boucle creation dimages pour les articles
            for ($j=1; $j < mt_rand(2,5); $j++) { 
                $image = new Image();
                $image->setUrl($faker->imageUrl())
                ->setCaption($faker->sentence())
                ->setAd($ad);
                $manager->persist($image);
            }





            // le manager persist les données 
            $manager->persist($ad);
        }






        // le manager enregistre en base de données
        $manager->flush();
    }
}
