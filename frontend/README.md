# New Frontend

This is a remaster of the original frontend.

Built with my new wip stack: tanstack router, tanstack query, tailwind and zustand.


The code is not that great as its the first one I've made with this new stack. It will be cleaned up slowly but surely in the future.

## Ordre des choses à faire

- Login doit pouvoir utiliser l'email (attention il doit manquer qqchose parce que j'arrive à me logguer mais ça 401 direct après sur un endpoint, le LexikJWT authenticator pète et si on surcharge le username dans le jwt ça pète dans le navigateur le parsing)

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


EN FAIT => C'est plutot un endpoint de config plus général, qui est loadé avant même l'instanciation de l'app react et qui set son bordel dans zustand. Cela permet toute la config style "spotifyEnabled" permet de savoir si y'a des creds et donc afficher le bouton "recherche sur spotify" quand on fais une musique dans le karaoke, etc...