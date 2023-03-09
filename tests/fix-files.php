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

/*
 |--------------------------------------------------------------------------
 | Add custom router to HttpKernel
 |--------------------------------------------------------------------------
 */
$file = __DIR__ . '/blog/app/Http/Kernel.php';
$parts = explode("class Kernel extends HttpKernel\n{", file_get_contents($file));
$constructor = "class Kernel extends HttpKernel
{
    public function __construct(\Illuminate\Contracts\Foundation\Application \$app, \Illuminate\Routing\Router \$router)
    {
        parent::__construct(\$app, \LaravelSerializer\Framework\Routing\SerializerRouter::from(\$router));
    }
";
file_put_contents($file, "$parts[0]$constructor$parts[1]");
