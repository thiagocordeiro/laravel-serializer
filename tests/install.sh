#!/bin/bash

rm -rf blog

composer create-project laravel/laravel blog
php fix-files.php

composer require thiagocordeiro/laravel-serializer:dev-main -d blog/

rm blog/routes/api.php
cp fixtures/api.php blog/routes/
cp fixtures/Foo.php blog/app/Models/
cp fixtures/Type.php blog/app/Models/
cp fixtures/HttpSerializationTest.php blog/tests/Feature/
cp fixtures/serializer.php blog/config/


