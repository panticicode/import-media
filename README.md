# Import Media App with Jobs & Websockets

## Project Setup
Clone this repo
### Install Composer Dependencies:
```sh
composer install
```

### Make a copy of environment file
Go to the app folder

```sh
cd /path/to/your/app/folder
```
Copy .env.example to .env:
```sh
cp .env.example .env
```

### Run Database Migrations:
```sh
php artisan migrate 
```

### Generate Key:
```sh
php artisan key:generate 
```
INFO  Application key set successfully.

### Install NPM Dependencies:
```sh
npm install
```
## For Development

### Compile and Hot-Reload

```sh
npm run dev
```

## For Production

### Type-Check, Compile, and Minify:

To compile and minify the application for production, run:

```sh
npm run build
```

## Running the Application

### Real-Time Development

To start the project and see changes in real-time, follow these steps:

1. Open WAMP (or any other local development server).
2. Open a terminal and start the PHP server:

```sh
php artisan serve
  INFO  Server running on [http://127.0.0.1:8000].  
  Press Ctrl+C to stop the server
```

3. In a separate terminal, run:

```sh
npm run dev
  VITE v5.3.5  ready in 450 ms

  ➜  Local:   http://localhost:5173/
  ➜  Network: use --host to expose
  ➜  press h + enter to show help

  LARAVEL v11.19.0  plugin v1.0.5

  ➜  APP_URL: http://localhost:8000
```

### Running the websockets & queue

To start the websockets, follow these steps:

```sh
php artisan reverb:start
   INFO  Starting server on 0.0.0.0:8080 (localhost).  
```

4. In another terminal, run:

```sh
php artisan queue:work
   INFO  Processing jobs from the [default] queue.  
```
If everything is set up correctly, you can access your application at:

[http://localhost:8000](http://localhost:8000)

That's all.








