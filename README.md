# Introduction
- Project about about parking car, user can booking parking lot to keep car, and owner parking will manage parking lot

# Set up enviroment for BE project

1. set up docker https://www.digitalocean.com/community/tutorials/how-to-install-and-use-docker-on-ubuntu-22-04
2. install docker composer:  ```sudo apt  install docker-compose```
3. ```docker-compose up -d```
4. ```docker exec -it php bash```
4. ```composer install```
5. ```cp .env.example .env ```
6. accept link : http://localhost/
# Set up database
1. comandline: ```php artisan migrate:fresh --seed```
2. Connect database:
- localhost
- port: 3307
- Database name: laravel
- password: password
3. Debug for eloquen query database : https://laravel.com/docs/9.x/telescope#main-content
# Query database

## Connect AWS
1. Comanlne :ssh -i "keyforapi.pem" ubuntu@ec2-54-250-192-139.ap-northeast-1.compute.amazonaws.com
or: ```https://ap-northeast-1.console.aws.amazon.com/ec2/home?region=ap-northeast-1#ConnectToInstance:instanceId=i-0ea53592da06e81b1```