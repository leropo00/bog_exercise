# PHP Developer Challenge exercise

## Files mentioned in section 2.2 Deliverables in instructions:

Since Laravel framework was used for development and the project is containerized with docker,
all necessary code files from instructions are in  "laravel" subdirectory.


### 1.) PHP Files:

Since Laravel framework is used, these files are inside the folder "laravel/app".
Files are located based on the recommended directory structure for the Laravel framework.
Controller is located inside the folder "laravel/app/Http/Controllers" 
and Models are located inside the folder "laravel/app/Models".
Logic for games is located in  "laravel/app/Services/GamesService.php"


### 2.) MySQL or Migrations:

Since Laravel migration mechanism was used to create tables, 
The definitions for the two needed tables are inside the folder  "laravel/database/migrations"
in files:  "2025_04_26_110157_create_user_accounts_table.php" and "2025_04_26_110202_create_transactions_table.php".


The created tables are named 'user_accounts' (users from instructions) and 'transactions'.
Table 'user_accounts' should not be confused with table 'users',
table 'users' is one among the tables, that come out of the box with laravel framework.

To set up tables, the command `php artisan migrate`  should be used.
This is further described in the section 'How to setup'.

Two tables with the following SQL are created:

```
CREATE TABLE `user_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `balance` decimal(10,2) NOT NULL DEFAULT '100.00',
  `betted_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_accounts_username_unique` (`username`),
  UNIQUE KEY `user_accounts_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
```


```
CREATE TABLE `transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `bet_amount` decimal(10,2) NOT NULL DEFAULT '100.00',
  `game_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('processed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'processed',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transactions_transaction_id_unique` (`transaction_id`),
  KEY `transactions_user_id` (`user_id`),
  CONSTRAINT `transactions_user_id` FOREIGN KEY (`user_id`) REFERENCES `user_accounts` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci

```


### 3.) Error Handling:

The following logic is used in `/process-transaction` to handle errors:

First presence of a transaction with the same transaction_id and in status 'processed' is checked,
to continue with transaction processing, this should not be present.

If this condition passed and other validations pass
(valid input data, user can bet the amount based on balance)
we check if lock is present via Cache facade, for this transaction_id.
Reddis is used as Cache backend, as it is one of cache systems that supports atomic locks in Laravel:
https://laravel.com/docs/12.x/cache#atomic-locks
Lock duration is currently defined as the maximum possible duration of php script, based on php .ini setting.


After this, the logic for processing the bet transaction occurs, occurs in try/catch/finally statement.
Changes to user account balance, are always performed with the eloquent orm's
decrement/increment operations which are atomic operations and concurrency-safe.


The first operation in try/catch deducts the amount placed on a bet from the user balance.
This is done immediately so that the user can't place more bets than his balance would allow, while the long-running process runs.

A separate database field 'betted_amount' is used for additional safety here.
Laravel scope on Account  'available_balance', is implemented as 'balance' - 'betted_amount'
so 'betted_amount' is incremented for the value 'bet_amount', to account for the amount placed on the bet.
Additional protection that could also be implemented, 
is implementing a cron job, that would remove  'betted_amount',
in case an exception occurred and 'betted_amount', wasn't reverted back successfully,
by setting 'betted_amount' to 0, on 'user_accounts' which weren't updated for some time,
but have 'betted_amount' greater than 0.

Then game is played, with sleep(1) for simulating a longer running process.
If bet is lost, 0 is returned for winning, else winnings is returned as factor multiplied based on bet value.

From this betted amount is subtracted and according to winnings, the user account balance is changed.
All the following operations happen inside a transaction, so either they all succeed or fail.

If an exception occurs at any point, inside catch, the first operation is to revert (decrement) 'betted_amount',
in case 'betted_amount' was successfully changed before. 


## How to setup 
Docker is needed to run the containers. Move to the root folder of the project and run the command:

```
docker-compose up -d
```

For the backend container

```sh
# You can also run commands outside the container, just use the bottom command
# and replace sh, with the command needed
# enter inside the container
# command is run as the root user
docker exec  -u 0  -it bog-exercise-laravel sh
# create the env file from sample
cp .env.example .env
# check that files was created
ls -hals
# install dependencies in laravel
composer install
# key needs to be regenerated in .env file
php artisan key:generate
# run the database migrations
php artisan migrate
# set permissions back to the correct user
chown -R www-data /var/www
# leave the container
exit
```

If you plan to run code outside docker, make sure you change .env file properties regarding the database and Redis, so that they point to url, instead of their docker service name.
Based on dependencies used in docker-compose,  php 8.2, MySQL 8.0, and the latest Redisare needed.


## How to test the endpoints

I personally used Postman for testing the endpoints.
When using Postman for testing, don't forget to define the header:

```
'Accept': 'application/json'
```

If you run the project with docker and use the default settings in .env file,
endpoints are available at the following URLs, with sample json input provided.

Some other limitations on input parameters also apply:
- "bet_amount": minimum bet is implemented, this currently is defined as 10.0
- "initial_balance" is optional, but must be bigger than the minimum "bet_amount", default value is 100.0.
- "game_type", three different games are implemented: roulette, craps, slots


http://localhost:8000/api/create-account
```
{
    "username": "user",
    "email": "user@username.si",
    "initial_balance": 555
}
```

http://localhost:8000/api/process-transaction
```
{
    "user_id": 1,
    "bet_amount": "15",
    "transaction_id": "tnx-999111",
    "game_type": "slots"
}
```

## How to check the database state

If you wish to check the database state, you can use some external client.
I used DBeaver client. If database settings in .env file were not changed, the following settings
should be used in DBeaver. 

```
Url: jdbc:mysql://localhost:3306/bog_exercise
Server: localhost
Database: bog_exercise
Port: 3306
Username: username
Password: password
```

If using Dbeaver, two additional properties need to be set on the connection:
Right-click your connection, choose "Edit Connection"
On the "Connection settings" screen (main screen), click on "Edit Driver Settings"
Click on "Driver properties" Set these two properties: "allowPublicKeyRetrieval" to true and "useSSL" to false. 
This step is also described here: https://stackoverflow.com/a/59778108

