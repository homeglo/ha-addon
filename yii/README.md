<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://user-images.githubusercontent.com/3043754/199541715-9dedc46c-e4c0-493d-ad9b-9db862ca4a03.png" height="100px">
    </a>
    <h1 align="center">HomeGlo PHP</h1>
    <br>
</p>

DIRECTORY STRUCTURE
-------------------

      assets/             contains assets definition
      commands/           contains console commands (controllers)
      config/             contains application configurations
      controllers/        contains Web controller classes
      mail/               contains view files for e-mails
      models/             contains model classes
      runtime/            contains files generated during runtime
      tests/              contains various tests for the basic application
      vendor/             contains dependent 3rd-party packages
      views/              contains view files for the Web application
      web/                contains the entry script and Web resources



REQUIREMENTS
------------

- Docker


INSTALLATION
------------

### Install via Docker-compose

~~~
git clone https://github.com/rweisbein/homeglo-php
cd homeglo-php
docker-compose up -d
docker exec homeglo-php homeglo_init.sh
~~~

Now you should be able to access the application through the following URL:

~~~
http://localhost:8000/
~~~

CONFIGURATION
-------------

### .env file

Copy the `.env.example` to file `.env` and populate with real data.

```php
AIRTABLE_API_KEY=
AIRTABLE_BASE_ID=

HUE_CLIENT_ID=
HUE_CLIENT_SECRET=

DB_HOST=192.168.44.20:3306
DB_USER=root
DB_PASS=example
DB_NAME=homeglo_db

HG_CLOCK_INTERVAL_SECONDS=
```
