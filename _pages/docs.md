---
layout: page
title: Docs
fulltitle: Documentation
permalink: /docs/
description: ""
menu:
  - setup: 
      Installation       : "#installation"
      Getting Started    : "#getting-started"
      Commands 			 : "#commands"
  - commands:
      Migrations  	 	 : "#migrations"
  - get involved:
      Contributions    	 : "#contributions"
---

Installation
------------

**Optional**

Before run the composer install command, add the bin-dir config path inside your ```composer.json``` file:

{% highlight JSON %}
"config": {
    "bin-dir": "bin"
}
{% endhighlight %}

**With Composer:**

	composer require dsv/craftsman

If you specify the bin directory you can run the command like this:

	php bin/craftsman

If you don't, this package should be listed as a vendor binary, and it should be runned like:

	php vendor/bin/craftsman

---

Getting started
---------------

To view a list of all available Craftsman commands, you may use the list command:

	php craftsman list

**Help Screen**

Every command includes a help screen which displays the command's available arguments and options. To view a help screen from a command, simply add the name of the command with help:

	php craftsman help migration:run

---

Migrations
----------

Migration schemes are simple files that hold the commands to apply and remove changes to your database. It allows you to easily keep track of changes made in your app. They may create/modify tables or fields, etc. But they are not limited to just changing the schema. You could use them to fix bad data in the database or populate new fields.

###Generator

Generate a migration with the migration:generate command:

	php craftsman migration:generate create_users

If the migration name is in the form `create_XXX` or `modify_XXX` and is followed by a list of column names and types then a migration containing the appropriate `add_column` and `update_column` statements will be created.

**Example**

	php craftsman migration:generate create_users firstname:varchar lastname:varchar email:varchar active:smallint

Output:

{% highlight PHP startinline %}
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
?>
{% endhighlight %}

The migration file will be placed in your migration folder or any folder you specify instead of the default path with a version number as a prefix, which allows Codeigniter to determine the order of the migrations.

Now it's your turn to give the finishing touches before running this scheme. Check the [Database Forge documentation](https://codeigniter.com/user_guide/database/forge.html) for more information about CodeIgniter Migrations.

###Displaying info

You can display the current migration information with the comand:

	php craftsman migration:info

Output:

{% highlight Bash %}
 -- Craftsman Migration -- 
+-------------+-------------------------------------------+
| Config      | Value                                     |
+-------------+-------------------------------------------+
| Work        | info                                      |
| Environment | development                               |
| Module      | ci_system                                 |
| DB Version  | 0                                         |
+-------------+-------------------------------------------+
| Path        | application/migrations/                   |
+-------------+-------------------------------------------+
| Info Mode   | ---                                       |
| Action      | None                                      |
+-------------+-------------------------------------------+
{% endhighlight %}

Each migration command shows relevant information to perform some action.

###Running migrations

Migrations are designed to be mostly automatic, but you’ll need to know when to make migrations, when to run them, and the common problems you might run into. Here's a list of possible options.

**Latest**

Migrate to the latest version, the migration class will use the very newest migration found in the filesystem.

	php craftsman migration:latest

**Version**

Version can be used to roll back changes or step forwards programmatically to specific versions.

	php craftsman migration:version 1

###Rolling-back migrations

Using the Codeigniter migration ideology, we are able to 'pull down' schema changes along with source code alterations. It allows you to quickly roll back and forth through the history of the schema, so as to work with desired version. These migrations are commonly used to easily alter state based on the environment you are in. Here's a list of possible options.

**Rollback the last migration operation**

	php craftsman migration:rollback

**Rollback all migrations**

	php craftsman migration:reset

**Rollback all migrations and run them all again**

	php craftsman migration:refresh

All migration versions of modules are stored apart from each other, so you can control the versions of every module and never interfering with each other.

---

Contributions
-------------

The Craftsman project welcomes, and depends, on contributions from developers and users in the CodeIgniter open source community. Contributions can be made in a number of ways, a few examples are:

* Code patches via pull requests
* Documentation improvements
* Bug reports and patch reviews

**Reporting an Issue?**

Please include as much detail as you can. Let us know your platform and Craftsman/CodeIgniter version. If the problem is visual (for example a theme or design issue) please add a screenshot and if you get an error please include the the full error and traceback.

**Submitting Pull Requests**

Once you are happy with your changes or you are ready for some feedback, push it to your fork and send a pull request. For a change to be accepted it will most likely need to have tests and documentation if it is a new feature.

---