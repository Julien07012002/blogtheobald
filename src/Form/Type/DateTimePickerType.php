<?php


namespace App\Form\Type;

use App\Utils\MomentFormatConverter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function Symfony\Component\String\u;

class DateTimePickerType extends AbstractType
{
    public function __construct(private MomentFormatConverter $formatConverter) 
    {
        // Le constructeur prend le convertisseur de format Moment.js en tant que dépendance.
    }

    
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['attr']['data-date-format'] = $this->formatConverter->convert($options['format']); // Format de date au format Moment.js.
        $view->vars['attr']['data-date-locale'] = u(\Locale::getDefault())->replace('_', '-')->lower(); // Locale par défaut au format "fr-fr".

        // Les attributs "data-date-format" et "data-date-locale" seront utilisés par le script JavaScript pour configurer le sélecteur de date.
    }

 
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'widget' => 'single_text', // Utilise un champ de texte simple pour l'entrée de date et d'heure.
            'html5' => false, // Désactive le rendu HTML5 pour le champ de date et d'heure.
        ]);
    }

    
    public function getParent(): ?string
    {
        return DateTimeType::class; // Hérite du type DateTimeType de base.
    }
}
