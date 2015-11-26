#!/usr/bin/make -f

.PHONY: lint check docs update force-update test clean prepare phploc pdepend phpmd phpcs phpcpd phpdox phpunit phpunit-jenkins jenkins

lint:
	find src -name '*.php' -exec php -l {} \;
	find test -name '*.php' -exec php -l {} \;

check: lint

docs: prepare
	doxygen doxygen.conf

composer.phar:
	curl -sS https://getcomposer.org/installer | php

vendor/autoload.php: update

composer.lock: composer.json composer.phar
	./composer.phar update

update: composer.phar
	./composer.phar install

force-update: composer.phar
	./composer.phar selfupdate
	./composer.phar update

test: phpunit

phpunit: vendor/autoload.php
	vendor/bin/phpunit -c phpunit.xml

clean:
	rm -fr build/api
	rm -fr build/coverage
	rm -fr build/logs
	rm -fr build/pdepend
	rm -fr build/phpdox
	rm -fr build/docs

prepare: vendor/autoload.php
	mkdir -p build/api
	mkdir -p build/coverage
	mkdir -p build/logs
	mkdir -p build/pdepend
	mkdir -p build/phpdox
	mkdir -p build/docs

phploc: prepare
	vendor/bin/phploc --count-tests --log-csv build/logs/phploc.csv --log-xml build/logs/phploc.xml src test

pdepend: prepare
	vendor/bin/pdepend --jdepend-xml=build/logs/jdepend.xml --jdepend-chart=build/pdepend/dependencies.svg --overview-pyramid=build/pdepend/overview-pyramid.svg src

phpmd: prepare
	vendor/bin/phpmd src xml build/phpmd.xml --reportfile build/logs/pmd.xml || echo

phpcs: prepare
	vendor/bin/phpcs --report=checkstyle --report-file=build/logs/checkstyle.xml --standard=PSR2 --extensions=php --ignore=autoload.php src || echo

phpcpd: prepare
	vendor/bin/phpcpd --log-pmd build/logs/pmd-cpd.xml src

phpdox: prepare
	vendor/bin/phpdox

jenkins: clean prepare lint phploc pdepend phpmd phpcs phpcpd phpunit phpdox docs
