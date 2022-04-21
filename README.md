# LAMP stack built with Docker Compose

A basic LAMP stack environment built using Docker Compose. It consists of the following:

* PHP
* Apache
* MySQL
* phpMyAdmin
* Redis

## Installation

* Clone this repository on your local computer
* Run the `docker-compose up -d`.
* Execute sql code from db.sql in phpMyAdmin

```shell
git clone https://github.com/Pawebf1/sam-php.git
cd sam-php/
sudo docker-compose up -d
// visit localhost:8080
// execute sql code from db.sql
// visit localhost
```
