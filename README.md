### Installation

```shell script
apt install make
git clone git@github.com:fogush/real-estate-searcher.git
cd real-estate-searcher
make docker-init
make docker-build-from-scratch
docker exec real-estate-searcher_workspace_1 bin/console doctrine:migrations:migrate -n
```

```shell script
docker login --username YOUR_USERNAME

```