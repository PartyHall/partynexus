# New Frontend

This is a remaster of the original frontend.

Built with my new wip stack: tanstack router, tanstack query, tailwind and zustand

## Ordre des choses à faire

- Fixer les 401 sur timelapse jsp pq (et s'assurer que ça marche pour les photos aussi)
- Faire la page "backdrops"
- Finir la portion Karaoké
- Faire la gallerie photo (!)
- Faire l'authentification oauth customizable
- Formulaire de mot de passe oublié
- Fonctionnalité de "créer un compte pour cet évenement"

## TODO

- Fix songs tsvector crap

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
