#!/bin/sh
set -e

# This is a simpler script to start the containers
# It is less verbose than the dev one as we assume a lot of stuff
# e.g. the database is ready, composer dependencies are installed, etc.

# If we run a consumer, just start it
# as the docker compose will ensure db is ready
# and the main container is run (thus migrations done)
if [ "$1" = 'consumer' ]; then
	exec php bin/console messenger:consume "$2" -vv
elif [ "$1" = 'main' ]; then
	# We flush the cache
	# NOTE: This should not be done like this
	# We should check if the version changed and only flush if it did
	# I can't be bothered to implement this right now so we'll just flush it
	# at every start
	php bin/console cache:clear --env=prod

	# First we migrate to the latest db version
	php bin/console doctrine:migrations:migrate --no-interaction --all-or-nothing

	# Then we ensure the JWT keys exists
	if [ ! "$( find ./config/jwt -iname '*.pem' -print -quit )" ]; then
		php bin/console lexik:jwt:generate-keypair
	fi

	# Lets also update meilisearch indexes
	php bin/console meili:import

	# We ensure the permissions are set correctly
	# for the var folder
	setfacl -R -m u:www-data:rwX -m u:"$(whoami)":rwX var
	setfacl -dR -m u:www-data:rwX -m u:"$(whoami)":rwX var

	# We load the JWT key so that the mercure server can use it
	export MERCURE_SUBSCRIBER_JWT_KEY=$(cat ./config/jwt/public.pem)

	# Finally we start the server
	exec docker-php-entrypoint frankenphp run --config /etc/caddy/Caddyfile
else
	echo 'Usage: \n'
	echo '/usr/local/bin/start.sh main - Starts the main web server\n'
	echo '/usr/local/bin/start.sh consumer <consumer name> - Starts one of the workers\n'
fi
