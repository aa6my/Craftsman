# Codeigniter Craftsman CLI #
---

[![Latest Stable Version](https://poser.pugx.org/dsv/craftsman/v/stable)](https://packagist.org/packages/dsv/craftsman) [![Total Downloads](https://poser.pugx.org/dsv/craftsman/downloads)](https://packagist.org/packages/dsv/craftsman) [![Latest Unstable Version](https://poser.pugx.org/dsv/craftsman/v/unstable)](https://packagist.org/packages/dsv/craftsman) [![License](https://poser.pugx.org/dsv/craftsman/license)](https://packagist.org/packages/dsv/craftsman)

Craftsman is the name of the command-line interface that you needed in Codeigniter 3.0. It provides a set of commands that will help you when you're developing your application, in addition to make your job easier. It is driven by the powerful Symfony Console component.

## Requirements 
---

* PHP 5.2.4+
* CodeIgniter 3.x
* Codeigniter [HMVC Module](https://bitbucket.org/wiredesignz/codeigniter-modular-extensions-hmvc)

Note: Codeigniter 2.x is not supported.

## How to install
---

### With composer 

```
composer require "dsv/craftsman":"*"
```

Before run the composer install command add the bin-dir config path inside your composer file:

```
"config": {
    "bin-dir": "bin"
}	
```
## How to use it
---

### Listing All Available Commands

To view a list of all available Craftsman commands, you may use the ```list``` command:

```
php bin/craftsman list
```

### Viewing The Help Screen For A Command 

Every command includes a "help" screen which displays the command's available arguments and options. 
To view a help screen from a command, simply add the name of the command with help:

```
php bin/craftsman help migration:run
```

## List of commands
---

### Migrations 

Migration schemes are simple files that hold the commands to apply and remove changes to your database. It allows you to easily keep track of changes made in your app. They may create tables, modify tables or fields, etc. But they are not limited to just changing the schema. You could use them to fix bad data in the database or populate new fields.

#### Creating migrations

To create a migration, you may use the migration:generate command on the Craftsman CLI:

```
php bin/craftsman migration:generate create_users
```

The migration command can accept an array of database fields:

```
php bin/craftsman migration:generate create_foo name:varchar description:text amount:int
```

Any field separated with the ':' symbol then the field type.

The migration will be placed in your application/migrations folder or in any folder you specify instead of the default value, and will contain a number which allows the framework to determine the order of the migrations.

#### Running migrations

Running Migrations (the CI way)

Here's the posible options for running the migrations:

**Current**

Whatever is set for $config['migration_version'] in HMVC ```config/migration.php``` file.

```
php bin/craftsman migration:run current
```

**Latest**

Migrate to the latest version. This works much the same way as current() but instead of looking for the $config['migration_version'] the Migration class will use the very newest migration found in the filesystem.

```
php bin/craftsman migration:run latest
```

**Version**

Version can be used to roll back changes or step forwards programmatically to specific versions. 

Note: It works just like current() but ignores $config['migration_version']

```
php bin/craftsman migration:run version 1
```

## CHANGELOG
---


## Codeigniter developers

This is a list of people you need to check on what they are working.

[Kenjis](https://github.com/kenjis/)