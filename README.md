Configuration
    `composer install`
    `bin/console make:migrate`
    `bin/console doctrine:database:create`
    `bin/console doctrine:migrations:migrate`

Run
    `php -S 127.0.0.1:8080 -t public`

Test
    `bin/phpunit`
