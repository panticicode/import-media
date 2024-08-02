# Import Media App with Jobs & Websockets

This application allows users to import media files, process them through jobs, and receive real-time updates via websockets using Reberb.

## Project Setup

### 1. Clone this repo

### 2. Install Composer Dependencies:
```sh
composer install
```
<img src="./git-images/composer.png" width="400">
### 3. Make a copy of environment file
Go to the app folder

```sh
cd /path/to/your/app/folder
```
Copy .env.example to .env:
```sh
cp .env.example .env
```
<img src="./git-images/env.png" width="400">
### 4. Run Database Migrations:
```sh
php artisan migrate 
```
During migration it will ask for create new database choose yes if db not exist
<img src="./git-images/migrations.png" width="400">

### 5. Generate Key:
```sh
php artisan key:generate 
```
<img src="./git-images/generate-key.png" width="400">

### 6. Install NPM Dependencies:
```sh
npm install
```
<img src="./git-images/npm-install.png" width="400">

## For Production

### Type-Check, Compile, and Minify:

To compile and minify the application for production, run:

```sh
npm run build
```
<img src="./git-images/npm-build.png" width="400">

## For Development

### Compile and Hot-Reload

```sh
npm run dev
```
<img src="./git-images/npm-dev.png" width="400">

## Running the Application

### Real-Time Development

To start the project and see changes in real-time, follow these steps:

1. Open WAMP (or any other local development server).
2. Open a terminal and start the PHP server:

```sh
php artisan serve
```

<img src="./git-images/serve.png" width="400">

3. In a separate terminal, run:

```sh
npm run dev
```

<img src="./git-images/npm-dev.png" width="400">

### Running Websockets & Queue

To start the websockets, follow these steps:

```sh
php artisan reverb:start
```
<img src="./git-images/reverb.png" width="400">

4. In another terminal, run:

```sh
php artisan queue:work
```

<img src="./git-images/queue.png" width="400">

## Environment Configuration

Ensure your .env file is correctly configured for your local development environment. Key settings include:

- Database connection settings
- Websockets configuration
- Queue driver settings

## Accessing the Application

If everything is set up correctly, you can access your application at:

[http://localhost:8000](http://localhost:8000)

That's all.



