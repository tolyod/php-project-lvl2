install:
	composer install

lint:
	composer run-script phpcs -- --standard=PSR12 src bin

lint-fix:
	composer run-script phpcbf -- --standard=PSR12 src tests

console:
	psysh --config psysh.php
update:
	composer update
test:
	composer run-script phpunit tests
