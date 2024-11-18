# Making a release

In order to make a release, each of these steps should be followed:

- Updating the Makefile to change the version number
- Update the `contrib` folders with new `compose.yaml` and `.env`
- Ensure linting & tests are passing (make tests & make lint)
- Make the final commit for the release
- `git tag -a "v"0.1.9"" -m ""0.1.9""`
- `git push --follow-tags`
- **Wait for the GHA to build the image**
- Edit the release on Github to add the changelog
- Update the docs to match the new feature
- Update the docs' `docs/partynexus/getting-started.md` to match the correct link for the `contrib` folder
- Build+release on the Github pages repository

The tags for the docs should be in the format: `Pn0.0.0-Ph0.0.0-[BUILD_ID]`.

With the first version number being the current PartyNexus version, the second the PartyHall version number and the build id an incremental value for each documentation update.
