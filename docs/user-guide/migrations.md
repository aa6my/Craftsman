#Migrations

Migration schemes are simple files that hold the commands to apply and remove changes to your database. It allows you to easily keep track of changes made in your app. They may create/modify tables or fields, etc. But they are not limited to just changing the schema. You could use them to fix bad data in the database or populate new fields.

---

##Creating migrations

Create a migration with the **migration:generate** command:

```
php craftsman migration:generate create_users
```

If the migration name is of the form "create_XXX" or "modify_XXX" and is followed by a list of column names and types then a migration containing the appropriate add_column and update_column statements will be created.

```
php craftsman migration:generate create_users firstname:varchar lastname:varchar email:varchar active:smallint
```

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

The migration file will be placed in your **migration** folder or any folder you specify instead of the default path. It will contain a version number as a prefix which allows **Codeigniter** to determine the order of the migrations.

Now it's your turn to give the finishing touches before running this scheme. Check the [Database Forge documentation](http://www.codeigniter.com/user_guide/database/forge.html) for more information about **CodeIgniter Migrations**.

---

##Displaying info

You can display the current migration information with the comand:

```
php craftsman migration:info
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

Each migration command shows relevant information to perform some action.

---

##Running migrations

Migrations are designed to be mostly automatic, but you’ll need to know when to make migrations, when to run them, and the common problems you might run into. Here's a list of possible options.

#### Latest

Migrate to the latest version, the migration class will use the very newest migration found in the filesystem.

```
php craftsman migration:latest
```

#### Version

Version can be used to roll back changes or step forwards programmatically to specific versions. 

```
php craftsman migration:version 1
```

## Rolling Back Migrations

Using the Codeigniter migration ideology, we are able to 'pull down' schema changes along with source code alterations. It allows you to quickly roll back and forth through the history of the schema, so as to work with desired version. These migrations are commonly used to easily alter state based on the environment you are in. Here's a list of possible options.

###Rollback the last migration operation

```
php craftsman migration:rollback
```

#### Rollback all migrations

```
php craftsman migration:reset
```

#### Rollback all migrations and run them all again

```
php craftsman migration:refresh
```

**Note**: all migration versions of modules are stored apart from each other, so you can control the versions of every module and never interfering with each other.
