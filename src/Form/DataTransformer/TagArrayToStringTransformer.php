<?php


namespace App\Form\DataTransformer;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Symfony\Component\Form\DataTransformerInterface;
use function Symfony\Component\String\u;

class TagArrayToStringTransformer implements DataTransformerInterface
{
    public function __construct(
        private TagRepository $tags
    ) {
    }
    // Le constructeur prend le TagRepository en tant que dépendance.


    public function transform($tags): string
    {
        // Cette méthode prend un tableau de tags en entrée et les transforme en une seule chaîne de caractères en les séparant par des virgules.
        return implode(',', $tags);
    }

    
    public function reverseTransform($string): array
    {
        // Cette méthode prend une chaîne de caractères en entrée et la transforme en un tableau de tags.
        if (null === $string || u($string)->isEmpty()) {
            return [];
        }

        // Sépare la chaîne par des virgules, supprime les espaces inutiles et filtre les noms de tags uniques.
        $names = array_filter(array_unique(array_map('trim', u($string)->split(','))));

        // Recherche les tags existants dans la base de données en fonction des noms.
        $tags = $this->tags->findBy([
            'name' => $names,
        ]);

        // Identifie les noms de tags qui n'existent pas encore dans la base de données et crée de nouveaux tags pour eux.
        $newNames = array_diff($names, $tags);
        foreach ($newNames as $name) {
            $tag = new Tag();
            $tag->setName($name);
            $tags[] = $tag;
        }

        return $tags;
    }
}