<?php

namespace Grnspc\Addresses\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Jackpopp\GeoDistance\GeoDistanceTrait;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Grnspc\Addressable\Models\Address.
 *
 * @property int                                                $id
 * @property int                                                $addressable_id
 * @property string                                             $addressable_type
 * @property string                                             $given_name
 * @property string                                             $family_name
 * @property string                                             $label
 * @property string                                             $organization
 * @property string                                             $line_1
 * @property string                                             $line_2
 * @property string                                             $city
 * @property string                                             $province
 * @property string                                             $postal_code
 * @property string                                             $country_code
 * @property array                                              $extra
 * @property float                                              $latitude
 * @property float                                              $longitude
 * @property bool                                               $is_primary
 * @property bool                                               $is_billing
 * @property bool                                               $is_shipping
 * @property \Carbon\Carbon|null                                $created_at
 * @property \Carbon\Carbon|null                                $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $addressable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\Grnspc\Addresses\Models\Address inCountry($countryCode)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grnspc\Addresses\Models\Address inLanguage($languageCode)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grnspc\Addresses\Models\Address isBilling()
 * @method static \Illuminate\Database\Eloquent\Builder|\Grnspc\Addresses\Models\Address isPrimary()
 * @method static \Illuminate\Database\Eloquent\Builder|\Grnspc\Addresses\Models\Address isShipping()
 * @method static \Illuminate\Database\Eloquent\Builder|\Grnspc\Addresses\Models\Address outside($distance, $measurement = null, $latitude = null, $longitude = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grnspc\Addresses\Models\Address whereAddressableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grnspc\Addresses\Models\Address whereAddressableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grnspc\Addresses\Models\Address whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grnspc\Addresses\Models\Address whereComplement($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grnspc\Addresses\Models\Address whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grnspc\Addresses\Models\Address whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grnspc\Addresses\Models\Address whereExtra($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grnspc\Addresses\Models\Address whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grnspc\Addresses\Models\Address whereIsBilling($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grnspc\Addresses\Models\Address whereIsPrimary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grnspc\Addresses\Models\Address whereIsShipping($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grnspc\Addresses\Models\Address whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grnspc\Addresses\Models\Address whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grnspc\Addresses\Models\Address whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grnspc\Addresses\Models\Address whereLine1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grnspc\Addresses\Models\Address whereLine2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grnspc\Addresses\Models\Address whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grnspc\Addresses\Models\Address whereOrganization($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grnspc\Addresses\Models\Address wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grnspc\Addresses\Models\Address whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grnspc\Addresses\Models\Address whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Grnspc\Addresses\Models\Address within($distance, $measurement = null, $latitude = null, $longitude = null)
 * @mixin \Eloquent
 */
class Address extends Model
{
    use GeoDistanceTrait;
    use SoftDeletes;

    protected const FLAGS = [
        'primary',
        'billing',
        'shipping'
    ];

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'addressable_id',
        'addressable_type',
        'label',
        'given_name',
        'family_name',
        'organization',
        'line_1',
        'line_2',
        'city',
        'province',
        'postal_code',
        'country_code',
        'extra',
        'latitude',
        'longitude',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'addressable_id' => 'integer',
        'addressable_type' => 'string',
        'label' => 'string',
        'given_name' => 'string',
        'family_name' => 'string',
        'organization' => 'string',
        'line_1' => 'string',
        'line_2' => 'string',
        'province' => 'string',
        'city' => 'string',
        'postal_code' => 'string',
        'country_code' => 'string',
        'extra' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
        'deleted_at' => 'datetime',
    ];

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = config('grnspc.addresses.tables.addresses', 'addresses');
        $this->updateFillables();
    }

    /**
     * {@inheritdoc}
     */
    protected static function booted()
    {
        static::saving(function (self $address) {
            if (config('grnspc.addresses.geocoding.enabled')) {
                $address->geocode();
            }
        });
    }

    /**
     * Update fillable fields dynamically.
     *
     * @return void.
     */
    private function updateFillables()
    {
        $columns  = preg_filter('/^/', 'is_', config('grnspc.addresses.flags', self::FLAGS));
        $this->mergeFillable($columns);
    }

    /**
     * Get the validation rules.
     *
     * @return array
     */
    public static function getValidationRules(): array
    {
        $rules = config('grnspc.addresses.rules', [
            'label'             => ['nullable', 'string', 'max:150'],
            'given_name'        => ['nullable', 'string', 'max:150'],
            'family_name'       => ['nullable', 'string', 'max:150'],
            'organization'      => ['nullable', 'string', 'max:150'],
            'line_1'            => ['required', 'string', 'max:255'],
            'line_2'            => ['nullable', 'string', 'max:255'],
            'city'              => ['required', 'string', 'max:150'],
            'province'          => ['required', 'string', 'max:150'],
            'postal_code'       => ['required', 'string', 'max:150'],
            'country_code'      => ['required', 'alpha', 'size:2', 'country'],
            'latitude'          => ['nullable', 'numeric'],
            'longitude'         => ['nullable', 'numeric'],
        ]);

        foreach (config('grnspc.addresses.flags', self::FLAGS) as $flag)
            $rules['is_' . $flag] = ['boolean'];

        return $rules;
    }

    /**
     * Get the owner model of the address.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function addressable(): MorphTo
    {
        return $this->morphTo('addressable', 'addressable_type', 'addressable_id', 'id');
    }

    /**
     * Scope primary addresses.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsPrimary(Builder $builder): Builder
    {
        return $builder->where('is_primary', true);
    }

    /**
     * Scope billing addresses.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsBilling(Builder $builder): Builder
    {
        return $builder->where('is_billing', true);
    }

    /**
     * Scope shipping addresses.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsShipping(Builder $builder): Builder
    {
        return $builder->where('is_shipping', true);
    }

    /**
     * Scope shipping addresses.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsSite(Builder $builder): Builder
    {
        return $builder->where('is_site', true);
    }

    /**
     * Scope addresses by the given country.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param string                                $countryCode
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInCountry(Builder $builder, string $countryCode): Builder
    {
        return $builder->where('country_code', $countryCode);
    }

    /**
     * Scope addresses by the given language.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param string                                $languageCode
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInLanguage(Builder $builder, string $languageCode): Builder
    {
        return $builder->where('language_code', $languageCode);
    }

    /**
     * Get full name attribute.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return implode(' ', [$this->given_name, $this->family_name]);
    }

    public function geocode(): self
    {
        $geocoding_api_key = config('grnspc.addresses.geocoding.api_key');

        if (!($query = $this->getQueryString()) && !$geocoding_api_key)
            return $this;

        $url = "https://maps.google.com/maps/api/geocode/json?address={$query}&sensor=false&key={$geocoding_api_key}";

        if ($geocode = file_get_contents($url)) {
            $output = json_decode($geocode);

            if (count($output->results) && isset($output->results[0])) {
                if ($geo = $output->results[0]->geometry) {
                    $this->latitude = $geo->location->lat;
                    $this->longitude = $geo->location->lng;
                }
            }
        }

        return $this;
    }

    /**
     * Get the encoded query string.
     *
     * @return string
     */
    public function getQueryString(): string
    {
        $query = [];

        $query[] = $this->line_1                            ?: '';
        //  $query[] = $this->line_2                        ?: '';
        $query[] = $this->city                              ?: '';
        $query[] = $this->province                          ?: '';
        $query[] = $this->postal_code                       ?: '';
        $query[] = country($this->country_code)->getName()  ?: '';

        $query = trim(implode(',', array_filter($query)));

        return urlencode($query);
    }


    /**
     * Get the address as array.
     *
     * @return array
     */
    public function getArray(): array
    {
        $address = $one = $two = [];

        $one[] = $this->line_1       ?: '';
        $one[] = $this->line_2       ?: '';

        $two[] = $this->city            ?: '';
        $two[] = $this->province        ?: '';
        $two[] = $this->postal_code     ?: '';

        $address[] = implode(', ', array_filter($one));
        $address[] = implode(' ', array_filter($two));
        $address[] = country($this->country_code)->getName() ?: '';

        if (count($address = array_filter($address)) > 0)
            return $address;

        return [];
    }

    /**
     * Get the address as html block.
     *
     * @return string
     */
    public function getHtml(): string
    {
        if ($address = $this->getArray())
            return '<address>' . implode('<br />', array_filter($address)) . '</address>';

        return '';
    }

    /**
     * Get the address as a simple line.
     *
     * @param  string  $glue
     * @return string
     */
    public function getLine($glue = ', '): string
    {
        if ($address = $this->getArray())
            return implode($glue, array_filter($address));

        return '';
    }

    /**
     * Get the country name.
     *
     * @return string
     */
    public function getCountryNameAttribute(): string
    {
        if ($this->country_code)
            return country($this->country_code)->getName();

        return '';
    }
}
