#!/bin/bash

composer install
vendor/bin/phpunit --configuration phpunit.xml
