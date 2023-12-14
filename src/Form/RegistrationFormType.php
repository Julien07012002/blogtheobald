<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Configuration du formulaire pour l'inscription d'un utilisateur.
        $builder
            ->add('username', TextType::class) // Champ de saisie du nom d'utilisateur.
            ->add('email', EmailType::class) // Champ de saisie de l'adresse e-mail.
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Password'], // Champ de saisie du mot de passe.
                'second_options' => ['label' => 'Repeat Password'], // Champ de saisie de confirmation du mot de passe.
            ])
            ->add('fullName', TextType::class); // Champ de saisie du nom complet.
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Aucune option par défaut spécifique.
        ]);
    }
}