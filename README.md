# Laravel-Translator

Laravel-serializer serializes json body from requests into Value Objects and Value Objects into json responses. It creates factories to given objects and uses [thiagocordeiro/serializer](https://github.com/thiagocordeiro/serializer) to create encodes and decoders.

### Installation

You just have to require the package

```bash
composer require thiagocordeiro/laravel-serializer
```

### Setting up 
This package register its provider automatically,
[See laravel package discover](https://laravel.com/docs/10.x/packages#package-discovery). However if for any reason the provider doesn't get registered, then you can register the provider manually in `config/app.php` file:

```php
return [
    ...
    'providers' => [
        ...
        LaravelSerializer\Framework\Providers\RequestSerializationProvider::class,
        ...
    ]
]
```

Just by adding the provider, you'll be able to configure and inject value objects into controllers, however, in orther to return these objects, it is necessary to change `Kernel.php` with a custom router configuration, so Laravel will be able to serialize configured objects as responses.

Open `App\Http\Kernel` and add a `__constructor` according to the snipped above:
```php
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Routing\Router;
use LaravelSerializer\Framework\Routing\SerializerRouter;

class Kernel extends HttpKernel
{
    public function __construct(Application $app, Router $router)
    {
        parent::__construct($app, SerializerRouter::from($router));
    }

    ...
}
```

### Configure value objects
Once the provider is registered and Kernel uses SerializerRouter, you'll have to configure the objects you want to have serialization enabled.

Open or create the configuration file `config/serializer.php` and add all the objects that should be serialized according to the following snippet:

```php
return [
    App\MyObject::class => [], //
];
```

### Usage
Say you have an `App\Order` object and you want it to be serialized:

###### With public properties
```php
<?php

namespace App;

readonly class Order
{
    public function __construct(
        public int $customerId,
        public Address $delivery,
        public DateTime $placedAt,
        public ProductCollection $products,
    ) {
    }
}
```

###### Without private properties
```php
<?php

namespace App;

class Order
{
    private int $customerId;
    private Address $delivery;
    private DateTime $placedAt;

    /** @var Product[] */
    private array $products;

    public function __construct(int $customerId, Address $delivery, DateTime $placedAt, Product ...$products)
    {
        $this->customerId = $customerId;
        $this->delivery = $delivery;
        $this->placedAt = $placedAt;
        $this->products = $products;
    }

    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    public function getDelivery(): Address
    {
        return $this->delivery;
    }

    public function getPlacedAt(): DateTime
    {
        return $this->placedAt;
    }

    /**
     * @return Product[]
     */
    public function getProducts(): array
    {
        return $this->products;
    }
}
```

First thing is to add into `config/serialization.php` (See previous section).

```php
use App\Order;

return [
    ...
    Order::class => [
        // 'encoder' => app(MyOrderCustomerEncoder::class),
        // 'decoder' => app(MyOrderCustomerDecorder::class),
    ],
];
```

Then in the routing file, inject or return the `App\Order` file into the routing:

```php
use App\Order;

Route::post('/', function (Order $create) {
    ...
});

Route::get('/', function (): Order {
    return new Order(...);
});
```

### Advance configuration

You might have advanced encoder and decoders to your Value Objects whenever necessary. please refer to [thiagocordeiro/serializer](https://github.com/thiagocordeiro/serializer) documentation in order to have these configuration.

### Note
1. Value objects should not have complex logic as well as no infrastructure coupling, in other words, they should be dummy objects with constructors, private properties and getters. [thiagocordeiro/serializer](https://github.com/thiagocordeiro/serializer) does't rely on public properties.
2. There is no need to add inherited objects into `config/serialization.php`, however they will get created at runtime which might have effect as soon as the application warms up, by adding inherited objects into the configuration file, they might get created during cache clear command.

### Contributing
Feel free to open issues, suggest changes and submit PRs :)

### Supporting
If you feel like supporting changes then you can send donations to the address below.

Bitcoin Address: bc1qfyudlcxqnvqzxxgpvsfmadwudg4znk2z3asj9h
