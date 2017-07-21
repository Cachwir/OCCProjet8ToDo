# OCCProjet8ToDo
Projet n°8 du parcours Développeur PHP/Symfony : améliorer une application en TDD

Version 1.0

Author: Cachwir

### how to install

- Pull the project (git clone https://github.com/Cachwir/OCCProjet8ToDo.git)
- you can rename the folder or leave it as it is.
- cd OCCProjet8ToDo or whatever you named it
- run composer install to install the dependancies
- follow this guide for permissions depending on your os : http://symfony.com/doc/current/setup/file_permissions.html (add some add some chmod -R 777) to the following folders :
   - var
- (if not completed through composer install) cd app/config and copy parameters.yml.dist to parameters.yml
- fill parameters.yml with your own config
- create your database using ./bin/console doctrine:schema:create
- (optionnal) add default data using ./bin/console doctrine:fixtures:load
- configure your virtual server if you need it. It needs to point to the web folder at the root of the project.
- enjoy~

### run tests

##### Prerequisites :

- install the fixtures (using ./bin/console doctrine:fixtures:load)
- have phpunit installed on your server (instructions on https://phpunit.de/manual/current/en/installation.html#installation.phar)

To run the tests, use the command "phpunit".<br>
To run only the functional tests, use "phpunit --group functional".<br>
To run only the unit tests, use "phpunit --group unit".

To export the tests, use "phpunit --coverage-html /path/to/the/desired/exported/file.html". Be careful as php needs to have the permission to create that file.