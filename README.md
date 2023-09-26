## Al-Fateh Inventory Management System

A Web Application for the user to track all the purchases and sales of their Products.

## Requirements
- PHP 8.1 and above
- Laravel 8
- phpmyadmin

## Installation
Install XAMPP or WAMPP for local server with following extension requirements:

- BCMath PHP Extension
- Ctype PHP Extension
- Fileinfo PHP Extension
- GD2 PHP Extension
- JSON PHP Extension
- Mbstring PHP Extension
- OpenSSL PHP Extension
- PDO PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension

### 1. Installation With XAMPP
 Go to C:\Windows\system32\drivers\etc\ open the "hosts" file in Administrator mode. Add the following code to it. Here

```
127.0.0.1 alfateh
```


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
