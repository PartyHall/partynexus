# New Frontend

This is a remaster of the original frontend.

Built with my new wip stack: tanstack router, tanstack query, tailwind and zustand

## Ordre des choses à faire

- Forgotten password => Créer la page frontend pour faire une demande de mail pour (+ ratelimit etc...)
- Login doit pouvoir utiliser pseudo OU email
- Backdrops: Ajout / modification d'un backdrop
- Backdrops: La modification de l'album ne rafraichis pas la page
- Karaoke: Chercher une musique pète sur les espaces (Dégager les tsvector et passer sur Meilisearch ou Typesense)
- Karaoke: Formulaire de créa / edit ne marche pas
- Karaoke: La décompilation ne rafraichis pas la page
- Karaoke: Upload de media
- Karaoke: Recherche spotify ne resize pas les images correctement
- Fonctionnalité de "créer un compte pour cet évenement"
- Fixer les 401 sur timelapse jsp pq (et s'assurer que ça marche pour les photos aussi)
- Faire l'authentification oauth customizable

## OAuth login & co

Il faut que je fasse un endpoint qui récupère:
- Les méthodes de login via OAuth (configurable dans le backend)
- Si les inscriptions sont ouvertes

Les méthodes de login il faudrait un truc dans le genre:
{
    "name": "some_slug"
    "buttonColor": "Some button color",
    "textColor: "Some text color",
    "icon": "b64 icon",
}

Après sur le backend on fait des routes au format `/api/oauth/{name}/login` et `/api/oauth/{name}/callback`
