# myApi

# Set Up
- git clone https://github.com/luboskruzik/myApi.git
- composer install
- cd docker/
- docker-compose up -d
- docker-compose exec web bash
- php bin/console doctrine:migrations:migrate
- php bin/console doctrine:fixtures:load

# API UI
- Go to https://editor.swagger.io/
- Import file openapi.yaml
- Use /login endpoint to obtain a token
- Authorize with the token value
- Use other endpoints

# Tests
- php bin/phpunit tests/Controller/ApiControllerTest.php


