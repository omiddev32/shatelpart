<?php

namespace App\Product\Entities;

use App\Core\BasicEntity;
use App\Country\Entities\Country;
use Lapaliv\BulkUpsert\Bulkable;

class ProductApi extends BasicEntity
{
    use Bulkable;
    
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The product connected to certain product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function connectedTo()
    {
        return $this->belongsTo(Product::class, 'connected_to', 'id');
    }

    /**
     * The product belongsTo a certain vendor.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function vendor()
    {
        return $this->belongsTo(\App\Vendor\Entities\Vendor::class);
    }

    /**
     * Get face values for this prduct.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function faceValues()
    {
        return $this->hasMany(FaceValueApi::class, 'product_id', 'product_id');
    }

    /**
     * Get the products name.
     *
     * @return string
     */
    public function getNameAttribute()
    {
        $name = $this->getRawOriginal('name');

        if($this->vendor_id == 2) {
            if($this->zone === 'Others') {
                $country = $this->getCountries(true);
                if($country) {
                    $name .= " - {$country->getTranslation('name', 'en')}";
                }
            } else if($this->zone === 'Global') {
                $name .= " - Global";
            }
        }

        return $name;
    }


    /**
     * Get Countires
     *
     * @return string
     */
    public function getCountries(bool $forTitle = false)
    {
        $countries = json_decode($this->countries, true);
        $attribute = $this->vendor_id == '2' ? 'original_id' : 'id';

        if($forTitle) {
            if(count($countries) == 1) {
                return Country::select(['id', 'name'])->where($attribute, $countries[0])->first();
            }

            return "";
        }

        return Country::whereIn($attribute, $countries);
    }
}
