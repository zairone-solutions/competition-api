# Uniquo Server

This repository contains a Dockerized setup for running the Uniquo Server along with Nginx, MySQL, Redis, phpMyAdmin and Laravel Horizon using Docker Compose.

## Requirements

- Docker
- Docker Compose

## Getting Started

1. Clone this repository:

    ```bash
    git clone https://github.com/alinaqi2000/uniquo-server.git
    ```

2. Navigate to the project directory:

    ```bash
    cd uniquo-server
    ```

3. Copy the example environment file and adjust the settings as needed:

    ```bash
    cp .env.example .env
    ```

4. Build and start the Docker containers:

    ```bash
    docker-compose up --build
    ```

5. Access Uniquo Server at [http://localhost:8000](http://localhost:8000)
   phpMyAdmin is available at [http://localhost:8001](http://localhost:8001)

## Services

- **PHP Service**: Runs the Uniquo Laravel application.
- **Nginx Service**: Web server for serving the Uniquo Laravel application.
- **MySQL Service**: Database service for MySQL.
- **Redis Service**: Redis service for caching.
- **phpMyAdmin Service**: Web-based MySQL administration tool.
- **Horizon Service**: Laravel Horizon service for managing job queues.

## Configuration

- **Nginx Configuration**: Nginx configuration files are located in the `nginx/conf.d/` directory.
- **MySQL Configuration**: MySQL configuration files can be placed in the `mysql/` directory.
- **PHP Configuration**: PHP configuration files can be added to the `php/` directory.

## License

This project is owned by Uniquo.
