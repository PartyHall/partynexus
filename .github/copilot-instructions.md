FOR FUCK SAKE STOP PROPOSING UGLY ASS HACKS

AND STOP FUCKING SAYING THAT YOU'RE GIVING ME A CLEAN STANDARD HACKLESS THING, THATS WHAT I EXPECT YOU TO FUCKING DO

---

## What's this project

PartyHall is an appliance-style software that have the following features:
- Photobooth
- Karaoke
- Quiz

This project is a sync server so that the appliance can:
- Send every picture taken
- Retreive configurations
  - Karaoke songs
  - Backdrops

## General Guidelines

- If the user encounters a problem help him debug and give you the information you need before drawing any conclusion on how to fix it.

## Backend

- We are using Symfony 7.3 alongside Api Platform 4.1.
- The database server is Postgres 16
- We have a Redis instance for workers
- Do not hesitate to check out the `composer.json` to find what's usable.

## Fontend

Currently the frontend present in `frontend/` is the old one and we are rewriting it properly in `/new_frontend`.

Unless specifically told by the user, you should be editing the new one.

- We are using React 19 with tailwind and a custom design system available in src/components. Not all components are fully complete or even available at all so check that those are ok before using them.
- We are running Tanstack Router alongside Tanstack Query which uses the "customFetch" method to handle the bulk of the generic stuff (auth, error handling, etc...)
- For forms we are using React Hook Form
- The general design of the app is a retrowave / synthwave.
  - We have a few colors already defined (mainly synthbg and primary).
  - We have glow utils that can be used to add glow effects to elements (text-COLOR-glow and box-COLOR-glow).

### Code style
- When doing callback in components, prefer inline arrow function instead of splitting it out unless its more than 3 lines