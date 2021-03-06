ToDoList
========
# How to install this project
## Prerequisites
You need to install [composer](https://getcomposer.org/) and [git](https://git-scm.com/) in order to use this project.
## Installation
- Clone the repository via git, or download it.
```
git clone https://github.com/Nabil001/todolistapp.git.
```
- Go to the project's root folder and run composer.
```
composer install
```
- Check that you have a parameters.yml located in app/config folder.
- Edit parameters.yml file, according to the desired settings.
```
parameters:
    database_host: [DATABASE HOST]
    database_port: [DATABASE PORT]
    database_name: [DATABASE NAME]
    database_user: [DATABASE USER]
    database_password: [DATABASE PASSWORD]
    secret: [KEY]

```
- In order to create the database that will be used by the app, run :
```
php bin/console doctrine:database:create
```
- Then, to implement the physical data model, run :
```
php bin/console doctrine:schema:update --force
```
