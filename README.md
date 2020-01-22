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

### Usage
```shell script
docker-compose exec workspace bin/console app:crawl
# OR
make docker-exec CONTAINER=workspace COMMAND="bin/console app:crawl"
```

### Testing
Add `--dry-run` to skip saving into the database and sending emails.
To check removing is working, copy some record in real_estate and change its link, for example:
```sql
INSERT INTO `real-estate-searcher`.`real_estate` (`link`, `price_dollars`, `number_of_rooms`, `address`, `floor`, `floors_total`, `area_total_cm`, `area_living_cm`, `area_kitchen_cm`, `year_construction`) VALUES ('TEST', '105000', '4', 'Минск, Одинцова ул., 48', '1', '9', '906000', '581000', '126000', '2005');
```
To check adding, mark any active record as deleted, for example:
```sql
UPDATE `real-estate-searcher`.`real_estate` SET `deleted`='1' WHERE  `id`=24;
```