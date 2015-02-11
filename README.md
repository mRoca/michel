SensioLabs Jobs
===============

## Installation

Install the vendors

	composer install

Create the folder app/sessions

	mkdir app/sessions
	chmod -R 755 app/sessions

## Config

### Configure the connect authentication

Create an application on connect, with the following parameters :

	Scopes : SCOPE_EMAIL SCOPE_PUBLIC
	Redirect URI : http://domain.name/session/callback

Complete the app/config/parameters.yml file :

	sensiolabs_connect.app_id: Id
	sensiolabs_connect.app_secret: Secret
	sensiolabs_connect.scope: SCOPE_EMAIL SCOPE_PUBLIC

### Authorize yourself as administrator

Login once, then update the `is_admin` user field in the DB.

## Usage

### Create DB and run migrations

	app/console doctrine:database:create
	app/console doctrine:migrations:migrate 

### Clean old deleted jobs

To delete from the database all announcements having the status « deleted » since at least x days (default: 20) :

	app/console jobboard:jobs:clean
	app/console jobboard:jobs:clean --days=20

### Clean old deleted jobs

Populate the elasticsearch index :

	app/console fos:elastica:populate

## API

The API is accessible if the current environment is dev, or only by those urls : 

* http://symfony.com
* http://sensiolabs.com

### GET /api/random

Returns a random job. 

    #/api/random
    {"company":"SensioLabs","country_name":"France","country_code":"FR","id":28,"title":"My great job 28","contract":"INTERNSHIP","url":"http:\/\/domain.name\/FR\/INTERNSHIP\/my-great-job-28"}

## Dev

Caution : The application provides jobs for every country, so all DateTime are stored in UTC format.
	
### Fill DB with fake data

	bin/fixtures.sh

### Extract translations

    app/console translation:extract fr --config=jobboard --keep --output-format=yml -v
    app/console translation:extract en --config=jobboard --keep --output-format=yml -v
