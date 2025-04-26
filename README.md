
## Exercise solution files

Mention that user accounts table



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
# create both env files
cp .env.example .env
# check that files were created
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

If you plan to rund code outside docker, make sure you change .env file properties regarding database.



## How to check database state

If you wish to check the database state, you can use some external client.
I used DBeaver client. If database settings in .env file were not changed, the following settings
should be used in DBeaver. 

Url: jdbc:mysql://localhost:3306/bog_exercise
Server: localhost
Database: bog_exercise
Port: 3306
Username: username
Password: password

I using Dbeaver, two additional properties need to be set on connection:
Right-click your connection, choose "Edit Connection"
On the "Connection settings" screen (main screen), click on "Edit Driver Settings"
Click on "Driver properties" Set these two properties: "allowPublicKeyRetrieval" to true and "useSSL" to false. This step is also described here: https://stackoverflow.com/a/59778108


## How to test the endponts

postman needs header

Accept 
	application/json


endpoints are the following

http://localhost:8000/api/create-account

// example json input

{
    "username": "user",
    "email": "user@username.si",
    "initial_balance": 555
}

http://localhost:8000/api/process-transaction

{
    "user_id": 1,
    "bet_ammount": "15",
    "transaction_id": "ccccc"
    "game_type": "blackjack"
}


            'user_id' => ['required', 'integer', 'exists:'. TABLE_USER_ACCOUNTS .',id'],
            'bet_ammount' => ['required', 'numeric', 'min:10.0'],
            'transaction_id' => ['required'],

            // TODO create an enum of game types
            'game_type' => ['required'],
