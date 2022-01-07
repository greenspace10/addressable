# GrnSpc Addresses

**GrnSpc Addresses** is a polymorphic Laravel package, for addressbook management. You can add addresses to any eloquent model with ease.
## Installation

1. Install the package via composer:
    ```shell
    composer require grnspc/addresses
    ```

2. Publish resources (migrations and config files):
    ```shell
    php artisan grnspc:publish:addresses
    ```

3. Execute migrations via the following command:
    ```shell
    php artisan grnspc:migrate:addresses
    ```

4. Done!


## Usage

To add addresses support to your eloquent models simply use `\Grnspc\Addresses\Traits\HasAddress` trait.

```php
<?php 

namespace App\Models;

use Grnspc\Addresses\Traits\HasAddresses;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasAddresses;

    // ...
}
```
## Adding an Address to a Model.
1. ### Method 1 - Via addAddress() Method
    ##### This method does a valadation check.
    ```php
    use App\Models\User;

    $user = User::find(1);
    $user->addAddress([
        'label'         => 'Default Address',
        'given_name'    => 'Nathan',
        'family_name'   => 'Robinson',
        'organization'  => 'GrnSpc',
        'line_1'        => '117 Banff Ave',
        'line_2'        => null,
        'city'          => 'Banff',
        'province'      => 'Alberta',
        'postal_code'   => 'T1L 1A4',
        'country_code'  => 'ca',
        'extra'         => [
                            'buzz_code' => '1234'    
                        ],
        'latitude'      => '51.1754012',
        'longitude'     => '-115.5715499',
        'is_primary'    => true,
        'is_billing'    => true,
        'is_shipping'   => true,
    ]);
    ```
2. ### Method 2 - Via Eloquent Relationship
    ```php
    use App\Models\User;

    $user = User::find(1);
    $user->addresses()->create([
        'label'         => 'Default Address',
        'given_name'    => 'Nathan',
        'family_name'   => 'Robinson',
        'organization'  => 'GrnSpc',
        'line_1'        => '117 Banff Ave',
        'line_2'        => null,
        'city'          => 'Banff',
        'province'      => 'Alberta',
        'postal_code'   => 'T1L 1A4',
        'country_code'  => 'ca',
        'extra'         => [
                            'buzz_code' => '1234'    
                        ],
        'latitude'      => '51.1754012',
        'longitude'     => '-115.5715499',
        'is_primary'    => true,
        'is_billing'    => true,
        'is_shipping'   => true,
    ]);
    ```
3. ### Method 3 - Create multiple new addresses
    ```php
    use App\Models\User;

    $user = User::find(1);
    $user->addresses()->createMany([
        [...],
        [...],
        [...],
    ]);
    ```
## Updating an Address on a Model
1. ### Method 1 - Via updateAddress() Method
    ```php
    $address = $user->addresses()->first();
    $newAttributes = [
        'label' => 'Default Work Address',
    ];
    $user->updateAddress($address, $newAttributes);
    ```
2. ### Method 2 - Via Eloquent Relationship
    ```php
    $address = $user->addresses()->first();
    $address->update([
        'label' => 'Default Work Address',
    ]);
    ```
## Deleting an Address on a Model
### Delete a Single Address
1. ### Method 1 - Via deleteAddress() Method
    ```php
    $address = $user->addresses()->first();

    $user->deleteAddress($address);
    ```
2. ### Method 2 - Via Eloquent Relationship
    ```php
    $address = $user->addresses()->first();
    $address->delete();
    ```
    Alternative way of address deletion
    ```php
    $user->addresses()->firstWhere('id', 123)->delete();
    ```
### Delete a All Address
1. ### Method 1 - Via flushAddress() Method
    ```php
    $user->flushAddresses();
    ```
2. ### Method 2 - Via Eloquent Relationship
    ```php
    $user->addresses()->delete();
    ```

## Address Facade
```php
use Grnspc\Addresses\Facades\Address;

$addresses = Address::all();
```
## Manage your Addresses on Model
The API is intuitive and very straight forward, so let's give it a quick look:

### Check if a Model has Addresses
```php
if ($user->hasAddresses()) {
    // Do something
}
```

### Get all Addresses for a Model
```php
// Method 1 (Collection)
$addresses = $user->addresses;

// Method 2 (Collection)
$addresses = $user->addresses()->get();

// Method 3 (Query Builder)
$addresses = $user->addresses();

// if a model only has one address
```

### Get Latest Addresses for a Model
```php
// address in order: only1 > is_primary > latest 
$address = $user->address;

// billing address in order: only1 > is_billing > latest
$address = $user->billing_address;

// shipping address in order: only1 > is_shipping > latest
$address = $user->shipping_address;
```

### Scoping and Getting Primary Addresses
```php
// return all primary addresses
$addresses = Address::isPrimary()->get()

// return all primary addresses for a model
$addresses = $user->addresses->isPrimary()->get();
```

### Scoping and Getting Billing Addresses
```php
// return all billing addresses
$addresses = Address::isBilling()->get()

// return all billing addresses for a model
$addresses = $user->addresses->isBilling()->get();
```
### Scoping and Getting Shipping Addresses
```php
// return all shipping addresses
$addresses = Address::isShipping()->get()

// return all shipping addresses for a model
$addresses = $user->addresses->isShipping()->get();
```

### Scoping Addresses by Country
```php
// return all addresses in country
$addresses = Address::InCountry('ca')->get()
```

### Find all addresses within 5 kilometers radius from the latitude/longitude 51.1754012/-115.5715499
```php
$fiveKmAddresses = User::findByDistance(5, 'kilometers', '51.1754012', '-115.5715499')->get();

// Alternative method to find users within certain radius
$user = new \App\Models\User();
$users = $user->lat('51.1754012')->lng('-115.5715499')->within(5, 'kilometers')->get();
```

## Changelog

Refer to the [Changelog](CHANGELOG.md) for a full history of the project.


## Contributing & Protocols

Thank you for considering contributing to this project! The contribution guide can be found in [CONTRIBUTING.md](CONTRIBUTING.md).

Bug reports, feature requests, and pull requests are very welcome.

- [Versioning](CONTRIBUTING.md#versioning)
- [Pull Requests](CONTRIBUTING.md#pull-requests)
- [Coding Standards](CONTRIBUTING.md#coding-standards)
- [Feature Requests](CONTRIBUTING.md#feature-requests)
- [Git Flow](CONTRIBUTING.md#git-flow)


## Security Vulnerabilities

If you discover a security vulnerability within this project, please submit an issue. All security vulnerabilities will be promptly addressed.


## License

This software is released under [The MIT License (MIT)](LICENSE).

(c) 2014-2022 GrnSpc, Some rights reserved.
