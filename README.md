
## Exercise solution files



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

## How to check database state

If you wish to check the database state, you can use some external client.
I used DBeaver client. If database settings in .env file were not changed, the following settings
should be used in DBeaver. 

Url: jdbc:mysql://localhost:3306/laravel_local
Server: localhost
Database: laravel_local
Port: 3306
Username: username
Password: password

I using Dbeaver, two additional properties need to be set on connection:
Right-click your connection, choose "Edit Connection"
On the "Connection settings" screen (main screen), click on "Edit Driver Settings"
Click on "Driver properties" Set these two properties: "allowPublicKeyRetrieval" to true and "useSSL" to false. This step is also described here: https://stackoverflow.com/a/59778108

