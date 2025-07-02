# New Frontend

This is a remaster of the original frontend.

Built with my new wip stack: tanstack router, tanstack query, tailwind and zustand

## Ordre des choses à faire

- Si modification de l'email = faire revalider le compte! (+ message sur l'ui pour le dire)
- Fixer les 401 sur timelapse jsp pq (et s'assurer que ça marche pour les photos aussi)
- Faire la page d'admin
- Finir la portion Karaoké
- Faire la gallerie photo (!)
- Faire l'authentification oauth customizable
- Formulaire de mot de passe oublié
- Fonctionnalité de "créer un compte pour cet évenement"

## TODO

- I need to translate the error on the backend based on user's "Accept-Language" header.
- Make custom snackbar to theme them (THANKS NOTISTACK to do weird shit like that so that I cant just override your css)
- Make a form wrapper for RHF to fill & show automatically the globalErrors
- Accessibility stuff
- Make the appliance communicate with the backend (e.g. is it online, etc...)
- Fix songs tsvector crap

GROS PROBLèME DE PERMISSION, le listing des event liste tout même si l'user est pas admin

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
