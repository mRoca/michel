SensioLabs Jobs
===============

## Installation

Install the vendors

	composer install

Create the folder app/sessions

	mkdir app/sessions
	chmod -R 755 app/sessions

## Dev

Caution : The application provides jobs for every country, so all DateTime are stored in UTC format.

### Create DB and update schema

	app/console doctrine:database:create
	app/console doctrine:schema:update --force
	
### Fill DB with fake data

	app/console doctrine:fixtures:load
