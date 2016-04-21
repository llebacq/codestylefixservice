# CODE STYLE FIX SERVICE

By [TheCodingMachine](http://www.thecodingmachine.com/)

## Description

This package makes usage of PhpCsFixer easy from PHP code ; for more information [check the PHP-CS-Fixer documentation](https://github.com/FriendsOfPHP/PHP-CS-Fixer)

## Installation

Add to ./composer.json file
```
"require": {
    "mouf/codestylefixservice"
  }
```
or
```
composer install mouf/codestylefixservice
```

## Usage

```php
$fixService = new FixService();
$fixService->csFix("MyPhpFile.php"); // add the path to file you want to auto-clean
```
