# prueba_lleego


## Install dependencies
```
composer install
```


## Use

Run server:
```
symfony server:start
```

Executing via console:
```
php bin/console lleego:avail MAD BIO 2023-06-01
```

Executing via API:
```
curl -H "Accept: application/json" "http://127.0.0.1:8000/api/avail?origin=MAD&destination=BIO&date=2022-06-01"
```


## Execute testing
```
php bin/phpunit
```