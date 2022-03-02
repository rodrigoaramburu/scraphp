




## Rodar testes

docker run --rm --net=host -p 4444:4444 -p 7900:7900 --shm-size="2g" selenium/standalone-chrome:4.1.2-20220217
php -S localhost:8881

composer test