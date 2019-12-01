### Installation

```
make docker-init
make docker-build-from-scratch
docker exec real-estate-searcher_workspace_1 bin/console doctrine:migrations:migrate -n
```