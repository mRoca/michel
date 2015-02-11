#!/bin/sh

php app/console doctrine:schema:drop --force
php app/console doctrine:schema:update --force
php app/console doctrine:fixtures:load
php app/console fos:elastica:populate

rm -Rf app/cache/*
rm -Rf app/sessions/*

mysql -u root sensiolabs_jobboard -e 'UPDATE sl_user SET admin = 1 WHERE uuid="a8a1c2f5-995d-41a1-b0ae-fd9a1157bef6"'
