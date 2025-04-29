# PHP Developer Challenge exercise

## Files mentioned is section 2.2 Deliverables in instructions:

Since laravel frameworks was used for development and project is containerized with docker,
all necessary code files from instructions are in  "laravel" subdirectory.


### 1.) PHP Files:

Since laravel framework is used, these files are inside folder "laravel/app".
Files are located based on recommended directrory structure for laravel framework.
Controller is located inside folder "laravel/app/Http/Controllers" 
and Models are located inside folder "laravel/app/Models".
Logic for games is located in  "laravel/app/Services/GamesService.php"


### 2.) MySQL or Migrations:

Since laravel migration mechanism was used to create tables, 
The definitions for the two needed tables are inside folder  "laravel/database/migrations"
in files:  "2025_04_26_110157_create_user_accounts_table.php" and "2025_04_26_110202_create_transactions_table.php".


The created tables are named 'user_accounts' (users from instructions) and 'transactions'.
Table 'user_accounts' should not be confused with table 'users',
these is one of tables, that come out of the box with laravel framework.

To setup tables, command  `php artisan migrate`  should be used.
This is further descriped in section  How to setup.

Two tables with following sql are created:

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

First presence of transaction with same transaction_id is checked,
to continue with process, this should not be present.

It this condition passed and other validations pass
(valid input data, user can bet the amount based on balance)
we check if lock via Cache facade is present, for this transaction_id.
Reddis is used as Cache backend, as it is one of cache systems that supports atomic locks in Laravel:
https://laravel.com/docs/12.x/cache#atomic-locks
Lock duration is currenlty defined to be max possbile duration of php script, based on php .ini setting.


After this the logic for processing bet transaction, occurs in try/catch/finally statement.
Changes to user account balance, are always performed with the eloquent orm,
decrement/increment operations which are atomic operations and concurrency-safe.


First operation in try/catch deducts amount placed on bet from user balance.
This is done immediatly, so that user can't place more bets, that his balance would allow, while the long running process runs.

A separate database field 'betted_amount' is used for additional safety here.
Laravel scope on Account  'available_balance', is implemented as 'balance' - 'betted_amount'
so 'betted_amount' is incremented for the value 'bet_amount', to account for amount placed on bet.
Additional protection that could also be implemented, 
is implementing a cron job, that would remove  'betted_amount',
in case an exception occured and 'betted_amount', wasn't reverted back succesfuly,
by setting 'betted_amount' to 0, on 'user_accounts' which weren't updated for some time,
but have 'betted_amount' greater than 0.

Then game is played, with sleep(1) for simulating longer running process.
If bet is lost, 0 is returned for winning, else winnings is returned as factor multiplied based on bet value.

From this betted amount is substraced and according to winnigs users balance is changed.
All the following operation happen inside a transaction, so either they all succed or fail.

If exception occurs at any point, inside catch, first operation is to revert (decrement) 'betted_amount',
in case 'betted_amount' was sucessfuly changed before. 


## How to setup 
Docker is needed to run the containers. Move to the root folder of project and run command:

```
docker-compose up -d
```

For the backend container

```sh
# you can also run commands outside container, just use the bottom command
# and replace sh, with the command needed
# enter inside container
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

If you plan to run code outside docker, make sure you change .env file properties regarding database. and redis, so that they point to url, instead to docker service name.
Based on dependencies used in docker-compose,  php 8.2, mysql 8.0, and latest reddis are needed.


## How to test the endponts

I personaly used postman for testing the endpoints.
When using postman for testing, don't forget to define header:
```
'Accept': 'application/json'
```

If you run project with docker and use the default settings in .env file,
endpoints are a available at following urls, with sample json input provided.

Some other limitations on input parameters also apply:
- "bet_amount": minimum bet is implemented, this currently is defined as 10.0
- "initial_balance" is optional, but must be bigger that minimum "bet_amount", default value is 100.0.
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

## How to check database state

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

If using Dbeaver, two additional properties need to be set on connection:
Right-click your connection, choose "Edit Connection"
On the "Connection settings" screen (main screen), click on "Edit Driver Settings"
Click on "Driver properties" Set these two properties: "allowPublicKeyRetrieval" to true and "useSSL" to false. 
This step is also described here: https://stackoverflow.com/a/59778108

