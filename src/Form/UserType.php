<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', null, ['label' => '*Email'])
            ->add('firstName', null, ['label' => '*Prénom'])
            ->add('lastName', null, ['label' => '*Nom'])
            ->add('pictureFile',FileType::class, [
                'label' => '*Image',
                 'mapped'=>false,
                 'constraints'=>[
                    new Image([
                        'mimeTypesMessage'=>'Veuillez soumetrre une image',
                        'maxSize'=>'1M', 
                        'maxSizeMessage'=>'Votre fait {{size}} {{suffix}}. la limite est de {{limit}} {{suffix}}',
                    ])
                 ]
                 ])
            ->add('password', PasswordType::class,['label' => '*Mot de Passe'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
