<?php

namespace App\System\Resources;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\{ID, DateTime, KeyValue, MorphToActionTarget, Status, Text, Textarea};
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;
use App\System\NovaResource;

class Log extends NovaResource
{
    /**
     * Get the logical group associated with the resource.
     *
     * @return string
     */
    public static function group()
    {
        return __("Logs");
    }

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __('Operation Reports');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('Operation Report');
    }

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<TActionModel>
     */
    public static $model = \Laravel\Nova\Actions\ActionEvent::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * Determine permission access this resource
     *
     * @var string
     */
    public static $permission = 'logs';

    /**
     * Indicates if the resource should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = false;

    /**
     * Indicates whether the resource should automatically poll for new resources.
     *
     * @var bool
     */
    public static $polling = true;

    /**
     * Determine if this resource is searchable.
     *
     * @return bool
     */
    public static function searchable()
    {
        return false;
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [

            ID::make(Nova::__('ID'), 'id')->showOnPreview(),

            Text::make(Nova::__('Action Name'), 'name', function ($value) {
                return Nova::__($value);
            })->showOnPreview(),

            Text::make(Nova::__('Action Initiated By'), function () {
                return $this->user->name ?? $this->user->email ?? __('Nova User');
            })->showOnPreview(),

            MorphToActionTarget::make(Nova::__('Action Target'), 'target')->showOnPreview(),

            Status::make(Nova::__('Action Status'), 'status', function ($value) {
                return Nova::__(ucfirst($value));
            })->loadingWhen([Nova::__('Waiting'), Nova::__('Running')])->failedWhen([Nova::__('Failed')]),

            $this->when(isset($this->original), function () {
                return KeyValue::make(Nova::__('Original'), 'original')->showOnPreview();
            }),

            $this->when(isset($this->changes), function () {
                return KeyValue::make(Nova::__('Changes'), 'changes')->showOnPreview();
            }),

            Textarea::make(Nova::__('Exception'), 'exception')->showOnPreview(),

            DateTime::make(Nova::__('Action Happened At'), 'created_at')->exceptOnForms()->showOnPreview(),
        ];
    }

    /**
     * Build an "index" query for the given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->with('user');
    }

    /**
     * Determine if the current user can create new resources.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    /**
     * Determine if the current user can edit resources.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function authorizedToUpdate(Request $request)
    {
        return false;
    }

    /**
     * Determine if the current user can replicate the given resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function authorizedToReplicate(Request $request)
    {
        return false;
    }

    /**
     * Determine if the current user can delete resources.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function authorizedToDelete(Request $request)
    {
        return false;
    }

    /**
     * Determine if this resource is available for navigation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public static function availableForNavigation(Request $request)
    {
        return true;
    }

    /**
     * Get the URI key for the resource.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'logs';
    }
}
