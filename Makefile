submodules:
	git submodule update --remote --force

vendor:
	composer install

check-configuration:
	php data/bin/check_configuration.php

tests: submodules vendor check-configuration
	php data/bin/symfony symfony:test --trace
	php vendor/bin/phpunit -c phpunit.xml
