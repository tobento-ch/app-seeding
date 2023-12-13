# App Seeding

Seeding support for the app using the [Seeder Service](https://github.com/tobento-ch/service-seeder).

## Table of Contents

- [Getting Started](#getting-started)
    - [Requirements](#requirements)
- [Documentation](#documentation)
    - [App](#app)
    - [Seeding Boot](#seeding-boot)
        - [Adding Seed Resources](#adding-seed-resources)
    - [Factories](#factories)
        - [Creating Factories](#creating-factories)
        - [Using Factories](#using-factories)
    - [Seeders](#seeders)
        - [Creating Seeders](#creating-seeders)
        - [Adding Seeders](#adding-seeders)
        - [Running Seeders](#running-seeders)
    - [User Seeding](#user-seeding)
- [Credits](#credits)
___

# Getting Started

Add the latest version of the app seeding project running this command.

```
composer require tobento/app-seeding
```

## Requirements

- PHP 8.0 or greater

# Documentation

## App

Check out the [**App Skeleton**](https://github.com/tobento-ch/app-skeleton) if you are using the skeleton.

You may also check out the [**App**](https://github.com/tobento-ch/app) to learn more about the app in general.

## Seeding Boot

The seeding boot does the following:

* [*SeedInterface*](https://github.com/tobento-ch/service-seeder#create-seed) implementation
* SeedersInterface implementation
* adds console commands for seeding

The following seeders will be available:

* [*Resource Seeder*](https://github.com/tobento-ch/service-seeder#resource-seeder)
* [*DateTime Seeder*](https://github.com/tobento-ch/service-seeder#datetime-seeder)
* [*User Seeder*](https://github.com/tobento-ch/service-seeder#user-seeder)

Keep in mind that no [*Resources*](https://github.com/tobento-ch/service-seeder#resources) are set as they may be specific to your app needs. Therefore, the seeders mostly using the [*Lorem Seeder*](https://github.com/tobento-ch/service-seeder#lorem-seeder) as fallback.

```php
use Tobento\App\AppFactory;
use Tobento\Service\Seeder\SeedInterface;
use Tobento\App\Seeding\SeedersInterface;

// Create the app
$app = (new AppFactory())->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'vendor', 'vendor');

// Adding boots
$app->boot(\Tobento\App\Seeding\Boot\Seeding::class);
$app->booting();

// Available Interfaces:
$seed = $app->get(SeedInterface::class);
$seeders = $app->get(SeedersInterface::class);

// Run the app
$app->run();
```

### Adding Seed Resources

You may add seeder resources by the following ways:

**Globally by using the app ```on``` method**

```php
use Tobento\App\AppFactory;
use Tobento\Service\Seeder\SeedInterface;
use Tobento\Service\Seeder\Resource;

// Create the app
$app = (new AppFactory())->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'vendor', 'vendor');
    
// Adding boots
$app->boot(\Tobento\App\Seeding\Boot\Seeding::class);
$app->booting();

// Add resources:
$app->on(SeedInterface::class, function(SeedInterface $seed) {

    $seed->resources()->add(new Resource('countries', 'en', [
        'Usa', 'Switzerland', 'Germany',
    ]));
});

$seed = $app->get(SeedInterface::class);

var_dump($seed->country());
// string(7) "Germany"

// Run the app
$app->run();
```

**Specific on any service using the seed**

```php
use Tobento\Service\Seeder\SeedInterface;
use Tobento\Service\Seeder\Resource;

class ServiceUsingSeed
{
    public function __construct(
        protected SeedInterface $seed,
    ) {
        $seed->resources()->add(new Resource('countries', 'en', [
            'Usa', 'Switzerland', 'Germany',
        ]));
    }
}
```

## Factories

### Creating Factories

You may create seed factories for testing or other purposes.

To create a factory, create a class that extends the ```AbstractFactory::class``` and configure your entity by using the ```definition``` method:

```php
use Tobento\App\Seeding\AbstractFactory;

class UserFactory extends AbstractFactory
{
    public function definition(): array
    {
        return [
            'firstname' => $this->seed->firstname(),
            'role' => 'admin',
        ];
    }
}
```

**Creating entities**

You may use the ```createEntity``` method to create specific entites using the definition. By default, a ```stdClass``` class will be created.

```php
use Tobento\App\Seeding\AbstractFactory;

class UserFactory extends AbstractFactory
{
    protected function createEntity(array $definition): object
    {
        return new User(
            firstname: $definition['firstname'],
        );
    }
}
```

**Storing entities**

By default, entities will not be stored. You may use the ```storeEntity``` method to store the entity based on the definition. In Addition, you may use ```getService``` method to get any service from the app container.

```php
use Tobento\App\Seeding\AbstractFactory;

class UserFactory extends AbstractFactory
{
    protected function storeEntity(array $definition): object
    {
        return $this->getService(UserRepositoryInterface::class)
            ->create($definition);
    }
}
```

### Using Factories

A factory can be created by calling the ```new``` method on the factory class:

```php
$factory = UserFactory::new();

// you may overwrite the default definition:
$factory = UserFactory::new(['role' => 'editor']);
```

**Make entities**

The ```make``` method creates an array of entities and returns them for further use in code, but does not store them in the database e.g.

```php
// make 10 entities:
$users = $factory->times(10)->make();

// make one:
$user = $factory->makeOne();
```

**Create entities**

The ```create``` method creates an array of entities, stores them in the database for instance and returns them for further use in the code.

```php
// create 10 entities:
$users = $factory->times(10)->create();

// create one:
$user = $factory->createOne();
```

**Raw entities**

The ```raw``` method creates an array of attributes from definition only and returns them for further use in code.

```php
// create 10:
$attributes = $factory->times(10)->raw();

// create one:
$attributes = $factory->rawOne();
```

**Modify definition**

You may modify the definition by using the ```modify``` method:

```php
use Tobento\Service\Seeder\SeedInterface;

$users = $factory
    ->modify(fn (SeedInterface $seed, array $definition) => [
        'email' => $seed->email(),
    ])
    ->times(10)
    ->make();
```

You may use the method inside your factory class:

```php
use Tobento\App\Seeding\AbstractFactory;
use Tobento\Service\Seeder\SeedInterface;

class UserFactory extends AbstractFactory
{
    public function withEmail(string $email): static
    {
        return $this->modify(fn(SeedInterface $seed, array $definition) => [
            'email' => $email
        ]);
    }
}

$users = $factory
    ->withEmail('admin@example.com')
    ->times(10)
    ->make();
```

**Modify entity**

You may modify the entity by using the ```modifyEntity``` method:

```php
use Tobento\Service\Seeder\SeedInterface;

$users = $factory
    ->modifyEntity(static function (SeedInterface $seed, object $user) {
        return $user->markAsDeleted();
    })
    ->times(10)
    ->make();
```

You may use the method inside your factory class:

```php
use Tobento\App\Seeding\AbstractFactory;
use Tobento\Service\Seeder\SeedInterface;

class UserFactory extends AbstractFactory
{
    public function deleted(string $email): static
    {
        return $this->modifyEntity(static function (SeedInterface $seed, object $user) {
            return $user->markAsDeleted();
        });
    }
}

$users = $factory
    ->deleted()
    ->times(10)
    ->make();
```

## Seeders

You may create a seeder class to easily seed your application with test data.

### Creating Seeders

To create a seeder, create a class that implements the ```SeederInterface::class```:

```php
use Tobento\App\Seeding\SeederInterface;

class UserSeeder implements SeederInterface
{
    public function run(): \Generator
    {
        foreach (UserFactory::new()->times(100)->create() as $user) {
            yield $user;
        }
    }
}
```

If you want to seed millions of test data, using factories my not be the fastest solution. Instead you may directly use the storage if available which is much faster.

Example using the user repository from the [App User](https://github.com/tobento-ch/app-user) bundle:

```php
use Tobento\App\Seeding\SeederInterface;
use Tobento\App\User\UserRepositoryInterface;
use Tobento\Service\Repository\Storage\StorageRepository;
use Tobento\Service\Iterable\ItemFactoryIterator;

class UserSeeder implements SeederInterface
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
    ) {
        if (! $userRepository instanceof StorageRepository) {
            throw new \InvalidArgumentException('Not supported ...');
        }
    }
    
    public function run(): \Generator
    {        
        yield from $this->userRepository
            ->query()
            ->chunk(length: 10000)
            ->insertItems(new ItemFactoryIterator(
                factory: function (): array {
                    return UserFactory::new()->definition();
                },
                create: 1000000,
            ));
    }
}
```

### Adding Seeders

You may add seeders to be run by the app console using the app ```on``` method.

```php
use Tobento\App\Seeding\SeedersInterface;

// ...

$app->on(
    SeedersInterface::class,
    static function (SeedersInterface $seeders): void {
        $seeders->addSeeder('users', UserSeeder::class);
    }
);
```

### Running Seeders

To run your added seeders use the ```seed``` console command.

```php
php app seed
```

**Run only specific seeders by its name**

```php
php app seed --name=users
```

**Display seeded entities**

You may display the seeded entities with the verbosity option:

```php
php app seed -v
```

**List all seeder names**

You may display the seeder names by using the ```seed:list``` console command.

```php
php app seed:list
```

## User Seeding

If you have installed the [User App](https://github.com/tobento-ch/app-user) bundle you may use the provided user factory and user seeder.

**User Factory**

```php
use Tobento\App\Seeding\User\UserFactory;

$user = UserFactory::new()
    ->withEmail('foo@example.com')
    ->withSmartphone('22334455')
    ->withUsername('Username')
    ->withPassword('123456')
    ->withRoleKey('admin') // 'guest' if role does not exist.
    ->withAddress(['firstname' => 'Firstname'])
    ->makeOne();
```

**User Seeder**

```php
use Tobento\App\Seeding\User\UserStorageSeeder;

$app->on(
    SeedersInterface::class,
    static function (SeedersInterface $seeders): void {
        $seeders->addSeeder('users', UserStorageSeeder::class);
    }
);
```

# Credits

- [Tobias Strub](https://www.tobento.ch)
- [All Contributors](../../contributors)