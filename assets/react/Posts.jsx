import React, { useEffect, useState } from 'react';
import { createRoot } from "react-dom/client";
import Pagination from "./components/Pagination";
import Tags from "./components/Tags";

function Posts({ baseUrl }) {
    // État local pour stocker la liste des messages
    const [posts, setPosts] = useState([]);
    // État local pour indiquer si le chargement est en cours
    const [loading, setLoading] = useState(true);
    // État local pour suivre la page actuellement affichée
    const [currentPage, setCurrentPage] = useState(1);
    // Le nombre d'éléments à afficher par page
    const itemsPerPage = 5;

    // Utilise useEffect pour charger les données au moment du rendu initial
    useEffect(() => {
        fetchDatas();
    }, []);

    // Fonction asynchrone pour récupérer les données depuis l'API
    const fetchDatas = async () => {
        try {
            // Effectue une requête à l'API pour obtenir les messages
            const response = await fetch('/fr/api/posts', {
                headers: {
                    'Accept': 'application/json'
                }
            }).then(r => r.json());

            // Met à jour l'état local avec les messages récupérés et indique que le chargement est terminé
            setPosts(response);
            setLoading(false);
        } catch (error) {
            console.log(error);
        }
    }

    // Fonction pour gérer le changement de page
    const handlePageChange = (page) => {
        // Mise à jour du numéro de page actuel lorsqu'un lien de pagination est cliqué
        setCurrentPage(page);
    }

    // Paginer les messages en fonction de la page actuelle
    const paginated = Pagination.getData(posts, currentPage, itemsPerPage);

    return (
        <>
            {/* Affiche "Chargement..." si les données sont en cours de chargement */}
            {loading && <>Chargement ...</>}
            {/* Affiche les messages paginés s'ils sont chargés */}
            {!loading && paginated.map(post => (
                <article className="post" key={post.id}>
                    {/* Affiche le titre du message avec un lien basé sur baseUrl */}
                    <h2>
                        <a href={baseUrl.replace('__slug__', post.slug)}>
                            {post.title}
                        </a>
                    </h2>
                    {/* Affiche la date de publication et l'auteur */}
                    <p className="post-metadata">
                        <span className="metadata">
                            <i className="fa fa-calendar"></i> {post.publishedAtLocal}
                        </span>
                        <span className="metadata"><i className="fa fa-user"></i> {post.author.fullName}</span>
                    </p>
                    {/* Affiche le résumé du message */}
                    <p>{post.summary}</p>
                    {/* Affiche les tags du message */}
                    <Tags list={post.tags} />
                </article>
            ))}
            {/* Affiche la pagination si le nombre d'éléments dépasse itemsPerPage */}
            {itemsPerPage < posts.length && (
                <Pagination
                    currentPage={currentPage}
                    itemsPerPage={itemsPerPage}
                    length={posts.length}
                    onPageChanged={handlePageChange}
                />
            )}
        </>
    );
}


class PostsElement extends HTMLElement {
    connectedCallback() {
        // Crée un point d'entrée React dans l'élément HTML personnalisé
        const root = createRoot(this);
        const baseUrl = this.dataset.url;
        // Rend le composant Posts en passant baseUrl comme prop
        root.render(<Posts baseUrl={baseUrl} />);
    }
}

// Définit un élément HTML personnalisé nommé 'posts-component'
customElements.define('posts-component', PostsElement);