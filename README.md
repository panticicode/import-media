# Import Media App with Jobs & Websockets

This application allows users to import media files, process them through jobs, and receive real-time updates via websockets using Reberb.

## Prerequisites

To run this web application, you need to have the following software installed:

- PHP 8.1+
- Node.js 20+
- npm 10+
- MySQL 8+
- Web Server (Apache2, Nginx, or any other web server)

<img src="./git-images/prerequisites.png" width="600">

## Project Setup

### 1. Clone this repo

for https use

```sh
git clone https://github.com/panticicode/import-media.git
```
for ssh use

```sh
git clone git@github.com:panticicode/import-media.git
```

### 2. Install Composer Dependencies:
```sh
composer install
```
<img src="./git-images/composer.png" width="600">

## 3. Make a copy of environment file
Go to the app folder
cd /path/to/your/app/folder

### Copy .env.example to .env:
```sh
cp .env.example .env
```
<img src="./git-images/env.png" width="600">

### 4. Run Database Migrations:
```sh
php artisan migrate 
```
During migration it will ask for create new database choose yes if db not exist

<img src="./git-images/migration.png" width="600">

### 5. Generate Key:
```sh
php artisan key:generate 
```
<img src="./git-images/generate-key.png" width="600">

### 6. Install NPM Dependencies:
```sh
npm install
```
<img src="./git-images/npm-install.png" width="600">

## For Production

### Type-Check, Compile, and Minify:

To compile and minify the application for production, run:

```sh
npm run build
```
<img src="./git-images/npm-build.png" width="600">

## Running the Application

### Real-Time Development, Compile and Hot-Reload

To start the project and see changes in real-time, follow these steps:

1. Open WAMP (or any other local development server you like).
2. Open a terminal and start the PHP server:

```sh
php artisan serve
```

<img src="./git-images/serve.png" width="600">

3. In a separate terminal, run:

```sh
npm run dev
```

<img src="./git-images/npm-dev.png" width="600">

### Running Websockets & Queue

To start the websockets, follow these steps:

```sh
php artisan reverb:start
```
<img src="./git-images/reverb.png" width="600">

4. In another terminal, run:

```sh
php artisan queue:work
```

<img src="./git-images/queue.png" width="600">

## Environment Configuration

Ensure your .env file is correctly configured for your local development environment. Key settings include:

- Database connection settings
- Websockets configuration
- Queue driver settings

## Accessing the Application

If everything is set up correctly, you can access your application at:

[http://localhost:8000](http://localhost:8000)

That's all.

<img src="./git-images/app.png" width="600">



