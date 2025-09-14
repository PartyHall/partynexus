# New Frontend

This is a remaster of the original frontend.

Built with my new wip stack: tanstack router, tanstack query, tailwind and zustand

## Ordre des choses à faire

- Forgotten password => Créer la page frontend pour faire une demande de mail pour (+ ratelimit etc...)
- Login doit pouvoir utiliser pseudo OU email
- Karaoke: Chercher une musique pète sur les espaces (Dégager les tsvector et passer sur Meilisearch ou Typesense)
- Karaoke: La décompilation ne rafraichis pas la page
- Karaoke: Upload de media
- Fonctionnalité de "créer un compte pour cet évenement"
- Faire l'authentification oauth customizable
- Refaire le système de formulaire comme dans Spectrum/Luminance

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
