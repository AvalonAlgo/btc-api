# BTC-API

## Overview

A simplified fake bitcoin transaction api made with Laravel. This is not real, I also wish I had a lot of bitcoin, alas, some things remain distant dreams forever

## System Requirements

- XAMPP w/ at least PHP v8.1 (you could install everything individually, but this is much simpler)
- Composer (https://getcomposer.org/download/)

## Installation Instructions

### Clone the Repository
```bash
git clone https://github.com/AvalonAlgo/btc-api
```

### Enter the repository and install the required packages with composer and npm
#### You could encounter some errors with composer unzipping. Go to your php.ini in your php directory and uncomment ';extension=zip' to 'extension=zip'
```bash
cd btc-api
npm install
composer install
```

### Create the database, run the application and navigate to the main page
```bash
php artisan migrate
php artisan db:seed
php artisan serve
http://127.0.0.1:8000/
```
