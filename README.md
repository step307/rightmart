# Log repository

## How to

### Initialisation

```console 
git clone https://github.com/step307/rightmart.git
cd rightmart
docker compose run composer composer install
```

### Start containers

```console 
docker compose up -d --wait
docker compose exec php bin/console doctrine:migration:migrate -n
```

### Run tests

```console
docker compose exec php bin/phpunit
```

### Execute the import command

```console
docker compose exec php bin/console app:log:import ./tests/E2e/Fixtures/logs.log
```
Could be run with `-v` or `-vvv` to get more information about the process

### Count the lines

via Swagger UI http://localhost:8080/api/doc
or
```console
curl -X 'GET' \
'http://localhost:8080/count?serviceNames%5B%5D=USER-SERVICE&serviceNames%5B%5D=INVOICE-SERVICE&statusCode=201&startDate=2017-08-17%2000%3A00%3A00&endDate=2018-08-18%2000%3A00%3A00' \
-H 'accept: application/json'
```