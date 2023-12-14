<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Configuration du formulaire pour l'édition d'un utilisateur.
        $builder
            ->add('username', TextType::class, [
                'label' => 'label.username', // Étiquette du champ de saisie du nom d'utilisateur.
                'disabled' => true, // Désactive la modification du nom d'utilisateur.
            ])
            ->add('fullName', TextType::class, [
                'label' => 'label.fullname', // Étiquette du champ de saisie du nom complet.
            ])
            ->add('email', EmailType::class, [
                'label' => 'label.email', // Étiquette du champ de saisie de l'adresse e-mail.
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class, // Classe d'entité associée au formulaire (User dans ce cas).
        ]);
    }
}