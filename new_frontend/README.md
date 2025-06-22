# New Frontend

This is a remaster of the original frontend.

Built with my new wip stack: tanstack router, tanstack query, tailwind and zustand

## TODO

- I need to translate the error on the backend based on user's "Accept-Language" header.
- I also need to modify my customFetch to send the appropriate language header based on preferences.
- Make custom snackbar to theme them (THANKS NOTISTACK to do weird shit like that so that I cant just override your css)
- Accessibility stuff
- Make the appliance communicate with the backend (e.g. is it online, etc...)
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
