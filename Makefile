vendor:
	composer self-update

check-configuration:
	php data/bin/check_configuration.php

tests: vendor check-configuration
	php data/bin/symfony symfony:test --trace
	php vendor/bin/phpunit -c phpunit.xml

