<?php

/*
 |--------------------------------------------------------------------------
 | Add package to composer repositories
 |--------------------------------------------------------------------------
 */
$file = 'blog/composer.json';
$content = json_decode(file_get_contents($file), true);
$content['repositories'] = [['type' => 'git', 'url' => 'https://github.com/thiagocordeiro/laravel-serializer']];
file_put_contents($file, json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
