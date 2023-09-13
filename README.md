## Inventory Management System

A dashboard for the user to view all stats of his devices, able to view history and set schedules.

## Requirements
- PHP 7.4 and above.
- Parse Server with MongoDB.

## Installation
Install XAMPP or WAMPP for local server setup
Open XAMPP Control panal and start [apache].

### 1. Using Composer
You can install the library via [Composer](https://getcomposer.org/). If you don't already have Composer installed, first install it by following one of these instructions depends on your OS of choice:
* [Composer installation instruction for Windows](https://getcomposer.org/doc/00-intro.md#installation-windows)
* [Composer installation instruction for Mac OS X and Linux](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx)


Now, you can easily keep the library up-to-date. After cloning the repository, you need to run int the terminal. 

```
composer install
```

Please see configuration section below for configuring SmartFill.

### 2. Manually

If you're not using Composer, you can also clone this repository into the directory of sample code that you just installed this repository and update the connection file.


Please see configuration section below for configuring for SmartFill.

## Configuration
After you setup SmartFill already. Next, you need to **configure** your congif file.  
So, we have Following files that you need to update with the Server IP and Database details:

- config.php

```
	$appID = <Application ID>;
    $masterKey = <Master Key>;
	$serverIP = <Server IP Address>;
    $port = <Server Port>;
```
