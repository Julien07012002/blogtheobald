<?php



namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ChangePasswordType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                'constraints' => [
                    new UserPassword(), // Vérifie que le mot de passe actuel est correct en utilisant UserPassword validator.
                ],
                'label' => 'label.current_password', // Étiquette pour le champ du mot de passe actuel.
                'attr' => [
                    'autocomplete' => 'off', // Désactive la complétion automatique du navigateur pour ce champ.
                ],
            ])
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class, // Type de champ pour le nouveau mot de passe.
                'constraints' => [
                    new NotBlank(), // Le nouveau mot de passe ne doit pas être vide.
                    new Length(
                        min: 5, // Longueur minimale du nouveau mot de passe.
                        max: 128, // Longueur maximale du nouveau mot de passe.
                    ),
                ],
                'first_options' => [
                    'label' => 'label.new_password', // Étiquette pour le champ du nouveau mot de passe.
                ],
                'second_options' => [
                    'label' => 'label.new_password_confirm', // Étiquette pour le champ de confirmation du nouveau mot de passe.
                ],
            ])
        ;
    }
}