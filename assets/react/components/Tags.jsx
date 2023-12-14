import React from 'react';

// Le composant Tags prend une seule prop appelée "list".
const Tags = (props) => {
    return (
        <p className="post-tags">
            {/* Mappe chaque élément de la liste des tags pour les afficher. */}
            {props.list.map(tag =>
                <a href="#"
                   className="label label-default"
                   key={tag.id}
                >
                    {/* Affiche une icône de tag suivie du nom du tag. */}
                    <i className="fa fa-tag"></i> {tag.name}
                </a>
            )}
        </p>
    );
};

// Exporte le composant Tags pour une utilisation ailleurs.
export default Tags;