<?php

namespace Grnspc\Addresses\Traits;

use Illuminate\Support\Collection;
use Grnspc\Addresses\Models\Address;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Grnspc\Addresses\Exceptions\FailedValidationException;

trait HasAddresses
{
    /**
     * Register a deleted model event with the dispatcher.
     *
     * @param \Closure|string $callback
     *
     * @return void
     */
    abstract public static function deleted($callback);

    /**
     * Define a polymorphic one-to-many relationship.
     *
     * @param string $related
     * @param string $name
     * @param string $type
     * @param string $id
     * @param string $localKey
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    abstract public function morphMany($related, $name, $type = null, $id = null, $localKey = null);

    /**
     * Get all attached addresses to the model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function addresses(): MorphMany
    {
        return $this->morphMany(config('grnspc.addresses.models.address'), 'addressable', 'addressable_type', 'addressable_id');
    }

    public function getAddressAttribute()
    {
        $addresses = $this->addresses();

        if ($addresses->count() === 1)
            return $addresses->first();

        if ($addresses->isPrimary()->count())
            return $addresses->firstWhere('is_primary', true);

        return $this->addresses()->latest()->first();
    }

    public function getBillingAddressAttribute()
    {
        $addresses = $this->addresses();

        if ($addresses->count() === 1)
            return $addresses->first();

        if ($addresses->isBilling()->count())
            return $addresses->firstWhere('is_billing', true);

        return $this->addresses()->latest()->first();
    }

    /**
     * Boot the addressable trait for the model.
     *
     * @return void
     */
    public static function bootAddressable()
    {
        static::deleted(function (self $model) {
            $model->addresses()->delete();
        });
    }

    /**
     * Check if model has addresses.
     *
     * @return bool
     */
    public function hasAddresses(): bool
    {
        return (bool) count($this->addresses);
    }

    /**
     * Add an address to this model.
     *
     * @param  array  $attributes
     * @return mixed
     * @throws Exception
     */
    public function addAddress(array $attributes)
    {
        $attributes = $this->loadAddressAttributes($attributes);

        return $this->addresses()->updateOrCreate($attributes);
    }

    /**
     * Updates the given address.
     *
     * @param  Address  $address
     * @param  array    $attributes
     * @return bool
     * @throws Exception
     */
    public function updateAddress(Address $address, array $attributes): bool
    {
        $attributes = $this->loadAddressAttributes($attributes);

        return $address->fill($attributes)->save();
    }

    /**
     * Deletes given address.
     *
     * @param  Address  $address
     * @return bool
     * @throws Exception
     */
    public function deleteAddress(Address $address): bool
    {
        return $this->addresses()->where('id', $address->id)->delete();
    }

    /**
     * Deletes all the addresses of this model.
     *
     * @return bool
     */
    public function flushAddresses(): bool
    {
        return $this->addresses()->delete();
    }

    /**
     * Get the primary address.
     *
     * @param  string  $direction
     * @return Address|null
     */
    public function getPrimaryAddress($direction = 'desc'): ?Address
    {
        return $this->addresses()
            ->primary()
            ->orderBy('is_primary', $direction)
            ->first();
    }

    /**
     * Get the billing address.
     *
     * @param  string  $direction
     * @return Address|null
     */
    public function getBillingAddress(string $direction = 'desc'): ?Address
    {
        return $this->addresses()
            ->billing()
            ->orderBy('is_billing', $direction)
            ->first();
    }

    /**
     * Get the first shipping address.
     *
     * @param  string  $direction
     * @return Address|null
     */
    public function getShippingAddress(string $direction = 'desc'): ?Address
    {
        return $this->addresses()
            ->shipping()
            ->orderBy('is_shipping', $direction)
            ->first();
    }

    /**
     * Add country id to attributes array.
     *
     * @param  array  $attributes
     * @return array
     * @throws FailedValidationException
     */
    public function loadAddressAttributes(array $attributes): array
    {
        // run validation
        $validator = $this->validateAddress($attributes);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $error  = '[Addresses] ' . implode(' ', $errors);

            throw new FailedValidationException($error);
        }

        // return attributes array with country_id key/value pair
        return $attributes;
    }

    /**
     * Validate the address.
     *
     * @param  array  $attributes
     * @return Validator
     */
    function validateAddress(array $attributes): Validator
    {
        $rules = (new Address)->getValidationRules();

        return validator($attributes, $rules);
    }


    /**
     * Find addressables by distance.
     *
     * @param string $distance
     * @param string $unit
     * @param string $latitude
     * @param string $longitude
     *
     * @return \Illuminate\Support\Collection
     */
    public static function findByDistance($distance, $unit, $latitude, $longitude): Collection
    {
        $addressModel = config('grnspc.addresses.models.address');
        $records = (new $addressModel())->within($distance, $unit, $latitude, $longitude)->get();

        $results = [];
        foreach ($records as $record) {
            $results[] = $record->addressable;
        }

        return new Collection($results);
    }
}
