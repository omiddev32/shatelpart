<?php

namespace App\User\Fields;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;

class Permissions extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'permissions-field';

    /**
     * Indicates if the element should be shown on the index view.
     *
     * @var \Closure|bool
     */
    public $showOnIndex = false;

    /**
     * The meta data for the element.
     *
     * @var array
     */
    public $meta = [
        'columns' => 1,
    ];

    /**
     * Specify the number of columns.
     *
     * @param array $columns
     * @return self
     */
    public function columns(int $columns)
    {
        return $this->withMeta(['columns' => $columns]);
    }

    /**
     * Disable type casting of array keys to numeric values to return the unmodified keys.
     *
     * @return $this
     */
    public function withGroups()
    {
        return $this->withMeta(['withGroups' => true]);
    }

    /**
     * Specify the available options
     *
     * @param array $options
     * @return self
     */
    public function options(array $options)
    {
        return $this->withMeta(['options' => $options]);
    }

    /**
     * Disable type casting of array keys to numeric values to return the unmodified keys.
     */
    public function withoutTypeCasting()
    {
        return $this->withMeta(['withoutTypeCasting' => true]);
    }

    /**
     * Determine if the array keys should be converted to numeric values.
     */
    private function shouldNotTypeCast()
    {
        return (
            array_key_exists('withoutTypeCasting', $this->meta)
            && $this->meta['withoutTypeCasting']
        );
    }

    /**
     * Hydrate the given attribute on the model based on the incoming request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $requestAttribute
     * @param  object  $model
     * @param  string  $attribute
     * @return void
     */
    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        if ($request->exists($requestAttribute)) {
            /**
             * When editing entries, they are returned as comma seperated string (unsure why).
             * As a result we need to include this check and explode the values if required.
             */            
            if (!is_array($choices = $request->input($requestAttribute))) {
                $permissions = collect(explode(',', $choices))->reject(function ($name) {
                    return empty($name);
                })->all();
            }

            $class = get_class($model);
            $permissions = array_merge($permissions , ["1"]);
            $class::saved(function ($model) use ($permissions) {
                $model->syncPermissions($permissions);
            });
        }
    }

    /**
     * Because we are having to explode the string value returned from Gamma/Vue, we assume that any
     * numeric value should be returned as such, instead of a string.
     *
     * @param  mixed  $value
     * @return mixed
     */
    private function castValueToType($value)
    {
        if (ctype_digit($value)) {
            return intval($value);
        }

        if (is_numeric($value)) {
            return floatval($value);
        }

        return $value;
    }
}