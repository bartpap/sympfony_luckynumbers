<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegisterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name' ,TextType::class, [
                'label' => 'Imię',
            ])
            ->add('email', EmailType::class,[
                'label' => 'E-mail',
                ])
            ->add('password', RepeatedType::class,[
                'type' => PasswordType::class,
                'first_options' => array('label' => 'Hasło'),
                'second_options' => array('label' => 'Powtórz hasło'),
                ])
            ->add('button', SubmitType::class,[
                'label' => 'Zarejestruj się',
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
