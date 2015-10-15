<div style="text-align: center">
	<img src="img/craftsman_logo.png" alt="alt text" width="120px" height="120px">
</div>

# Craftsman CLI

Craftsman is a command-line interface that provides a set of commands that will help you when you're developing your application, in addition to make your job easier. It is driven by the powerful Symfony Console component.

---

[![Latest Stable Version](https://poser.pugx.org/dsv/craftsman/v/stable)](https://packagist.org/packages/dsv/craftsman) [![Total Downloads](https://poser.pugx.org/dsv/craftsman/downloads)](https://packagist.org/packages/dsv/craftsman) [![Latest Unstable Version](https://poser.pugx.org/dsv/craftsman/v/unstable)](https://packagist.org/packages/dsv/craftsman) [![License](https://poser.pugx.org/dsv/craftsman/license)](https://packagist.org/packages/dsv/craftsman)

## Requirements 

* PHP 5.2.4+
* CodeIgniter 3.x

---

## Demo

You can install Craftsman, generate a migration scheme then update the database with no aditional installations. (3 minutes).

<iframe width="680" height="400" src="https://www.youtube.com/embed/JA51oVUoWRc?rel=0&amp;showinfo=0" frameborder="0" allowfullscreen></iframe>

---

## Installation

With composer:

```bash
composer require dsv/craftsman ^2.0
```

**Optional**: Before run the composer install command, add the bin-dir config path inside your composer file:

```json
"config": {
    "bin-dir": "bin"
}	
```

If you specify the bin directory you can run the command like this:

```bash
php bin/craftsman
```

If you don't, this CLI Composer package should be listed as a vendor binary, and it should be runned like:

```bash
php vendor/bin/craftsman
```
---

## How to use it

To view a list of all available Craftsman commands, you may use the **list** command:

```
php craftsman list
```

###Help Screen

Every command includes a **help** screen which displays the command's available arguments and options. To view a help screen from a command, simply add the name of the command with help:

```
php craftsman help migration:run
```
---

## Commands

There are several commands which you will use to interact with your application:

* [Migrations](user-guide/migrations/#running-migrations), which is responsible for applying migrations, as well as unapplying and listing their status.
* [Generate Migrations](user-guide/migrations/#creating-migrations), which is responsible for creating new migrations based on the changes you have made to your models.

---

## References

* [https://getcomposer.org/doc/articles/vendor-binaries.md](https://getcomposer.org/doc/articles/vendor-binaries.md)
