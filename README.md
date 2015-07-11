# Codeigniter Craftsman CLI #
---

[![Latest Stable Version](https://poser.pugx.org/dsv/craftsman/v/stable)](https://packagist.org/packages/dsv/craftsman) [![Total Downloads](https://poser.pugx.org/dsv/craftsman/downloads)](https://packagist.org/packages/dsv/craftsman) [![Latest Unstable Version](https://poser.pugx.org/dsv/craftsman/v/unstable)](https://packagist.org/packages/dsv/craftsman) [![License](https://poser.pugx.org/dsv/craftsman/license)](https://packagist.org/packages/dsv/craftsman)

Craftsman is the name of the command-line interface that you needed in Codeigniter 3.0. It provides a set of commands that will help you when you're developing your application, in addition to make your job easier. It is driven by the powerful Symfony Console component.

## Requirements 
---

* PHP 5.2.4+
* CodeIgniter 3.x

Note: Codeigniter 2.x is not supported.

## How to install
---

### With composer

```
composer require "dsv/craftsman":"^1.3"
```

**Note**: Before run the composer install command, add the bin-dir config path inside your composer file:

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

Every command includes a ```help``` screen which displays the command's available arguments and options. To view a help screen from a command, simply add the name of the command with help:

```
php bin/craftsman help migration:run
```

## List of commands
---
* [Migrations](#migrations)

## Migrations
---

Migration schemes are simple files that hold the commands to apply and remove changes to your database. It allows you to easily keep track of changes made in your app. They may create/modify tables or fields, etc. But they are not limited to just changing the schema. You could use them to fix bad data in the database or populate new fields.

### Creating migrations

Create a migration with the migration:generate command:

```
php bin/craftsman migration:generate create_users
```

The migration command may accept an array of database fields using the field format ```field_name:field_type``` :

```
php bin/craftsman migration:generate create_users firstname:varchar lastname:varchar email:varchar active:smallint
```

The migration file will be placed in your ```application/migrations``` folder or any folder you specify instead of the default path. It will contain a version number as a prefix which allows the Codeigniter framework to determine the order of the migrations.

Here's the example output:

```php
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Migration_create_users extends CI_Migration {

	public function __construct()
	{
		$this->load->dbforge();
		$this->load->database();
	}

	public function up() 
	{
		$this->create_users_table();
	}

	public function down() 
	{
		$this->dbforge->drop_table('ci_users');
	}

	private function create_users_table()
	{
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'null' => FALSE,
				'auto_increment' => TRUE
			),
			'firstname' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'null' => FALSE
			),
			'lastname' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'null' => TRUE
			),
			'email' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'null' => TRUE
			),
			'active' => array(
				'type' => 'SMALLINT',
				'null' => FALSE,
				'default' => 1
			),
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('ci_users',TRUE);		
	}
}

/* End of file 001_create_users.php.php */
/* Location: /application/migrations/001_create_users.php */
```

Now it's your turn to give the finishing touches before running this scheme, check the CI's Database Forge documentation:

[http://www.codeigniter.com/user_guide/database/forge.html](http://www.codeigniter.com/user_guide/database/forge.html)

### Display info

You can display the current migration information with the comand:

```
php bin/craftsman migration:info
```

This is an example output:

```
 -- Craftsman Migration -- 
+-------------+-------------------------------------------+
| Config      | Value                                     |
+-------------+-------------------------------------------+
| Work        | info                                      |
| Environment | development                               |
| Module      | ci_system                                 |
| DB Version  | 0                                         |
+-------------+-------------------------------------------+
| Path        | application/migrations/ 				  |
+-------------+-------------------------------------------+
| Info Mode   | ---                                       |
| Action      | None                                      |
+-------------+-------------------------------------------+
```

### Running migrations
 
Running Migrations respecting the Codeigniter standards. Here's a list of posible options.

#### Current

Whatever is set for ```$config['migration_version']``` in ```config/migration.php``` file.

```
php bin/craftsman migration:run current
```

#### Latest

Migrate to the latest version, the migration class will use the very newest migration found in the filesystem.

```
php bin/craftsman migration:latest
```

#### Version

Version can be used to roll back changes or step forwards programmatically to specific versions. 

```
php bin/craftsman migration:version 1
```

### Rolling Back Migrations

#### Rollback the last migration operation

```
php bin/craftsman migration:rollback
```

#### Rollback all migrations

```
php bin/craftsman migration:reset
```

#### Rollback all migrations and run them all again

```
php bin/craftsman migration:refresh
```

**Note**: all migration versions of modules are stored apart from each other, so you can control the versions of every module and never interfering with each other.

## CHANGELOG
---

**2.0.0**

* New independent arquitecture, fix some bugs, and new interface. 

**1.3.0**

* Now compatible with secured CI installation path (using --ci-route).
* Improved display migration info.

**1.2.3**

* Update Migration:run commands
* Now using Third-Party-Modules as HMVC pattern.

**1.2.1**

* Add php docblocks
* Fix some bugs.

**1.2.0**

* Add reset, refresh and rollback migration commands.

## Contribute!
---

If you like Craftsman CLI help me to improve it. Here's a list of something that you can do:

* Fork and clone.
* Create a branch, commit, push and send us a pull request.
* Check the list of [issues](https://gitlab.com/david-sosa-valdes/craftsman/issues).
* Send me an email.

## Related Projects for CodeIgniter 3.0

* [kenjis/codeigniter-cli](https://github.com/kenjis/codeigniter-cli)