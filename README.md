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

To add addresses support to your eloquent models simply use `\Grnspc\Addresses\Traits\Addressable` trait.

### Manage your addresses

```php
// Get instance of your model
$user = new \App\Models\User::find(1);

// Create a new address
$user->addresses()->create([
    'label' => 'Default Address',
    'given_name' => 'Nathan',
    'family_name' => 'Robinson',
    'organization' => 'GrnSpc',
    'line_1' => '117 Banff Ave',
    'line_2' => null,
    'city' => 'Banff',
    'province' => 'Alberta',
    'postal_code' => 'T1L 1A4',
    'country_code' => 'ca',
    'latitude' => '51.1754012',
    'longitude' => '-115.5715499',
    'is_primary' => true,
    'is_billing' => true,
    'is_shipping' => true,
]);

// Create multiple new addresses
$user->addresses()->createMany([
    [...],
    [...],
    [...],
]);

// Find an existing address
$address = app('grnspc.addresses.address')->find(1);

// Update an existing address
$address->update([
    'label' => 'Default Work Address',
]);

// Delete address
$address->delete();

// Alternative way of address deletion
$user->addresses()->where('id', 123)->first()->delete();
```

### Manage your addressable model

The API is intuitive and very straight forward, so let's give it a quick look:

```php
// Get instance of your model
$user = new \App\Models\User::find(1);

// Get attached addresses collection
$user->addresses;

// Get attached addresses query builder
$user->addresses();

// Scope Primary Addresses
$primaryAddresses = app('grnspc.addresses.address')->isPrimary()->get();

// Scope Billing Addresses
$billingAddresses = app('grnspc.addresses.address')->isBilling()->get();

// Scope Shipping Addresses
$shippingAddresses = app('grnspc.addresses.address')->isShipping()->get();

// Scope Addresses in the given country
$egyptianAddresses = app('grnspc.addresses.address')->InCountry('ca')->get();

// Find all users within 5 kilometers radius from the latitude/longitude 51.1754012/-115.5715499
$fiveKmAddresses = \App\Models\User::findByDistance(5, 'kilometers', '51.1754012', '-115.5715499')->get();

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

(c) 2014-2021 GrnSpc, Some rights reserved.
