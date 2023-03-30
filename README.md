# Buck-hill Pet-shop

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About This Project

This Project is a laravel project aimed at demonstration and access the skill level of the developer. 

## Installation
<p>Clone this Repository with</p>

```bash
git clone https://github.com/celxkodez/pet-shop.git
```
<p>navigate to the project directory.</p>

* Note: this project requires docker for easy setup.

#### with docker
<p>On the Project root directory, run the commands</p>

copy .env.example content to .env

if target machine is a unix based system, simply run
```bash
    cp .env.example .env
```

```bash
    make setup-application
```
The above command will install all dependency and perform all necessary
application setup processes.

to seed the database, simply use

```bash
    make artisan-command p=db:seed
```

after that, visit you can view the application on your local machine
with http://127.0.0.1:8000/ or http://localhost:8000/

if everything this is successful, visiting that url should return a json
response object with ``{"message": "application is up"}``.

## Contributing

Pull requests are welcome. For major changes, please open an issue first
to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License

[MIT](https://choosealicense.com/licenses/mit/)
