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

### Extract translations

    app/console translation:extract fr --config=jobboard --keep --output-format=yml -v
    app/console translation:extract en --config=jobboard --keep --output-format=yml -v

## Usage

### Clean old deleted jobs

To delete from the database all announcements having the status « deleted » since at least x days (default: 20) :

	app/console jobboard:jobs:clean
	app/console jobboard:jobs:clean --days=20 
