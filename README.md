### About
This application runs a command that parses real estate sites and sends updates.
For realt.by you need to create a request ("заявка", https://realt.by/account/requests/), copy its ID from URL 
(e.g. 39152 for https://realt.by/sale/flats/?request=39152) and put into the settings file below. 
Only tested for apartments. Offices, houses and rent may not work correctly

### Installation
Install Docker (https://docs.docker.com/install) and Docker Compose (https://docs.docker.com/compose/install/)
```shell script
sudo apt install make
git clone https://github.com/fogush/real-estate-searcher.git
cd real-estate-searcher
touch .env.local
# Then open .env.local and put necessary settings, see the section below
make install
# Setup any cron schedule:
crontab -e
# And add there something like this (5-21 - from 8:00 to 24:00 UTC+3):
0 5-21 * * * docker exec real-estate-searcher_workspace_1 bin/console app:crawl
```

Required settings. You may copy-paste them into `.env.local` and change values:
```ini
# Any random string, for example:
APP_SECRET=qufb7dWbSpUg8Bdexmoe72

# Your login (email) and password from realt.by
REALTBY_LOGIN=example@example.com
REALTBY_PASSWORD=example

# Request ID (Заявка) - your search filters
REALTBY_REQUEST_ID=99999

# A list of recipients who will receive updates. In JSON format
EMAIL_RECIPIENTS='["example@example.com", "example2@example2.com"]'

# A connection string for any email service. See example for gmail (just change login and password):
MAILER_URL=gmail://any_gmail_login:its_password@localhost
```

### Usage
```shell script
docker-compose exec workspace bin/console app:crawl
# OR
make docker-exec CONTAINER=workspace COMMAND="bin/console app:crawl"
```
The command has options:
  -a, --send-all        Send all parsed real estates, even already found. It makes sense for testing only
  -d, --dry-run         Do not send anything, but do all the same
  -r, --no-removed      Do not check which real estates are removed


### Manual testing
Add `--dry-run` to skip saving into the database and sending emails.
To check removing is working, copy some record in real_estate and change its link, for example:
```sql
INSERT INTO `real-estate-searcher`.`real_estate` (`link`, `price_dollars`, `number_of_rooms`, `address`, `floor`, `floors_total`, `area_total_cm`, `area_living_cm`, `area_kitchen_cm`, `year_construction`) VALUES ('TEST', '105000', '4', 'Минск, Одинцова ул., 48', '1', '9', '906000', '581000', '126000', '2005');
```
To check adding, mark any active record as deleted, for example:
```sql
UPDATE `real-estate-searcher`.`real_estate` SET `deleted`='1' WHERE  `id`=24;
```