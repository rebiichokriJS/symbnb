<?php

namespace App\Form;

use App\Entity\Image;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImageType extends AbstractType
{


        /**
     * Permet davoir la configuration de base d'un champ
     *
     * @param string $placeholder le placeholder d'un champ
     * @param array $options les options d'un champ
     * @return array
     */
    private function getConfiguration($placeholder, $options = []){
        return array_merge([
                'attr' => [
                    'placeholder' => $placeholder
            ]
        ], $options);
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('url', UrlType::class, $this->getConfiguration("Url de l'image"))
            ->add('caption', TextType::class, $this->getConfiguration("Titre de l'image"))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Image::class,
        ]);
    }
}
