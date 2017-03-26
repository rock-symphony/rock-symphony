check-configuration:
	php data/bin/check_configuration.php

tests: check-configuration
	php data/bin/symfony symfony:test --trace

