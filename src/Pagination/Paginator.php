<?php


namespace App\Pagination;

use Doctrine\ORM\QueryBuilder as DoctrineQueryBuilder;
use Doctrine\ORM\Tools\Pagination\CountWalker;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

class Paginator
{
    // La taille par défaut de la page.
    public const PAGE_SIZE = 10;

    private int $currentPage; // Le numéro de la page actuelle.
    private \Traversable $results; // Les résultats paginés.
    private int $numResults; // Le nombre total de résultats.

    public function __construct(
        private DoctrineQueryBuilder $queryBuilder, // Le constructeur prend un objet Doctrine QueryBuilder.
        private int $pageSize = self::PAGE_SIZE // La taille de la page par défaut.
    ) {
    }

    public function paginate(int $page = 1): self
    {
        // Calcule la page actuelle en s'assurant qu'elle soit au moins égale à 1.
        $this->currentPage = max(1, $page);

        // Calcule la position du premier résultat de la page actuelle.
        $firstResult = ($this->currentPage - 1) * $this->pageSize;

        // Crée une requête avec une limite et un décalage pour paginer les résultats.
        $query = $this->queryBuilder
            ->setFirstResult($firstResult)
            ->setMaxResults($this->pageSize)
            ->getQuery();

        // Si aucune jointure n'est spécifiée dans la requête, définissez CountWalker::HINT_DISTINCT sur false.
        if (0 === \count($this->queryBuilder->getDQLPart('join'))) {
            $query->setHint(CountWalker::HINT_DISTINCT, false);
        }

        // Utilise le Paginator de Doctrine pour paginer les résultats.
        $paginator = new DoctrinePaginator($query, true);

        // Détermine si les "output walkers" doivent être utilisés en fonction de la présence de clauses HAVING dans la requête.
        $useOutputWalkers = \count($this->queryBuilder->getDQLPart('having') ?: []) > 0;
        $paginator->setUseOutputWalkers($useOutputWalkers);

        // Récupère les résultats paginés sous forme d'itérateur.
        $this->results = $paginator->getIterator();

        // Récupère le nombre total de résultats.
        $this->numResults = $paginator->count();

        return $this;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getLastPage(): int
    {
        // Calcule le nombre total de pages en fonction du nombre total de résultats et de la taille de la page.
        return (int) ceil($this->numResults / $this->pageSize);
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function hasPreviousPage(): bool
    {
        // Vérifie s'il existe une page précédente.
        return $this->currentPage > 1;
    }

    public function getPreviousPage(): int
    {
        // Récupère le numéro de la page précédente.
        return max(1, $this->currentPage - 1);
    }

    public function hasNextPage(): bool
    {
        // Vérifie s'il existe une page suivante.
        return $this->currentPage < $this->getLastPage();
    }

    public function getNextPage(): int
    {
        // Récupère le numéro de la page suivante.
        return min($this->getLastPage(), $this->currentPage + 1);
    }

    public function hasToPaginate(): bool
    {
        // Vérifie s'il y a plus de résultats que la taille de la page.
        return $this->numResults > $this->pageSize;
    }

    public function getNumResults(): int
    {
        return $this->numResults;
    }

    public function getResults(): \Traversable
    {
        return $this->results;
    }
}