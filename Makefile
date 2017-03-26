vendor:
	composer install

check-configuration:
	php data/bin/check_configuration.php

tests: vendor check-configuration
	php vendor/bin/phpunit -c phpunit.xml
	php data/bin/symfony symfony:test --trace

