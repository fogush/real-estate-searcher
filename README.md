### Installation

```shell script
apt install make
git clone git@github.com:fogush/real-estate-searcher.git
cd real-estate-searcher
make docker-init
make docker-build-from-scratch
docker-compose exec workspace php composer.phar install
docker-compose exec workspace bin/console doctrine:migrations:migrate -n
```

```shell script
docker login --username YOUR_USERNAME

```

### Usage
```shell script
docker-compose exec workspace bin/console app:crawl
```