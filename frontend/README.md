# New Frontend

This is a remaster of the original frontend.

Built with my new wip stack: tanstack router, tanstack query, tailwind and zustand.


The code is not that great as its the first one I've made with this new stack. It will be cleaned up slowly but surely in the future.

## Ordre des choses à faire

- Attention, ça veut dire que n'importe qui qui a un compte sur l'IDP peut se créer un compte! Voir si c'est ce qu'on veut
- Trouver une solution pour fixer l'update des roles: si l'admin change les roles dans keycloak, il faut que l'utilisateur soit à jour sans avoir à se reconnecter. sinon ça veut dire que tu enlève le role admin a qqun et tant que le refresh token marche il aura le role à l'infini
