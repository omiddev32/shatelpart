<?php

namespace App\Packages\Settings\Http\Controllers;

use Laravel\Nova\Panel;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\{ResolvesFields, Nova};
use Laravel\Nova\Fields\FieldCollection;
use Illuminate\Support\Facades\Validator;
use Laravel\Nova\Contracts\Resolvable;
use Illuminate\Http\Resources\ConditionallyLoadsAttributes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Packages\Settings\ActionEvent;

class SettingsController extends Controller
{
	use ResolvesFields, ConditionallyLoadsAttributes;

    /**
     * Inertia page
     *
     * @param $request
     * @param $pageKey
     * @return json response
     */
    public function inertiaPage(Request $request, $pageKey)
    {
        $tool = $this->toolForKey($request, $pageKey);

        if(! $tool) abort(404);
        if (! $tool->authorizedToUpdate($request)) abort(403);

        return inertia('Nova.Settings', [
            'basePath'    => 'settings',
            'pageId'      => $pageKey,
            'breadcrumbs' => $tool->breadcrumbs($request),
        ]);
    }

    /**
     * Get page date when load page
     *
     * @param $request
     * @param $pageKey
     * @return json response
     */
	public function loadPage(Request $request, $pageKey)
	{
		$tool = $this->toolForKey($request, $pageKey);

		if(! $tool) abort(404);
		if (! $tool->authorizedToUpdate($request)) abort(403);

		$label = $tool->label();
		$saveButtonLabel = $tool->saveButtonLabel();
        $saveMessage = $tool->saveMessage();
		$fields = $this->assignToPanels($label, $this->availableFields($tool->fields($request)));
		$panels = $this->panelsWithDefaultLabel($label, $tool->fields($request));
		$model = $tool->model::with($tool->with)->where($tool->primaryKey(), $tool->primaryValue())->first();

        $addResolveCallback = function (&$field) use($model) {

            if (! empty($field->attribute)) {
                $fakeResource = $this->makeFakeResource($field->attribute, $model?->{$field->attribute});
                $field->resolve($fakeResource);
            }

            if (! empty($field->meta['fields'])) {
                foreach ($field->meta['fields'] as $_field) {
                    $fakeResource = $this->makeFakeResource($_field->attribute, $model?->{$field->attribute});
                    $_field->resolve($fakeResource);
                }
            }
        };

        $fields->each(function (&$field) use ($addResolveCallback) {
            $addResolveCallback($field);
        });

        return response()->json([
        	'label'           => $label,
            'panels'          => $panels,
            'fields' 	      => $fields,
            'saveButtonLabel' => $saveButtonLabel,
            'saveMessage'     => $saveMessage
        ], 200);
	}

    /**
     * Save page date
     *
     * @return void
     */
	public function savePage(NovaRequest $request, $pageKey)
    {
        $tool = $this->toolForKey($request, $pageKey);

        if(! $tool) abort(404);
        if (! $tool->authorizedToUpdate($request)) abort(403);

        $model = $tool->model::with($tool->with)->where($tool->primaryKey(), $tool->primaryValue())->firstOrNew();

        DB::connection($model->getConnectionName())->transaction(function () use ($request, $model, $tool) {
            $fields = $this->availableFields($tool->fields($request));

            $fields = $fields->map(function ($field) {
                if (!empty($field->attribute)) return $field;
                if (!empty($field->meta['fields'])) return $field->meta['fields'];
                return null;
            })->filter()->flatten();

            $rules = [];
            foreach ($fields as $field) {
                $field->resolve($this->makeFakeResource($field->attribute, $model?->{$field->attribute}), $field->attribute);
                $rules = array_merge($rules, $field->getUpdateRules($request));
            }

            Validator::make($request->all(), $rules)->validate();

            $fields->whereInstanceOf(Resolvable::class)->each(function ($field) use ($request, &$model) {
                if (empty($field->attribute)) return;
                if ($field->isReadonly(app(NovaRequest::class))) return;
                if (!empty($field->meta['translatable']['original_attribute'])) $field->attribute = $field->meta['translatable']['original_attribute'];

                $field->fill($request, $model);

                // if (!array_key_exists($field->attribute, $tempResource->getAttributes())) return;
                // $model->{$field->attribute} = $tempResource->{$field->attribute};
            });

            $tool::beforSave($request, $model);

            $model->save();
            $tool::afterSave($request, $model);

            DB::transaction(function () use ($request, $model) {
                Nova::usingActionEvent(function ( $actionEvent) use ($request, $model) {
                    $this->actionEvent = ActionEvent::forSettingsSave(Nova::user($request), $model);
                    $this->actionEvent->save();
                });
            });


        });

        return json_response([], 200);
    }

    /**
     * Make fake resource
     *
     * @param $fieldName string
     * @param $fieldValue
     * @return Fluent
     */
    protected function makeFakeResource(string $fieldName, $fieldValue)
    {
        $fakeResource = new \Laravel\Nova\Support\Fluent;
        $fakeResource->{$fieldName} = $fieldValue;
        return $fakeResource;
    }

    /**
     * Get the fields that are available for the given request.
     *
     * @return \Laravel\Nova\Fields\FieldCollection<int, \Laravel\Nova\Fields\Field>
     */
    protected function availableFields($fields)
    {
        return (new FieldCollection($this->filter($fields)))->authorized(request());
    }

    /**
     * Get the add fields that are available for the given request.
     *
     * @return \Laravel\Nova\Fields\FieldCollection<int, \Laravel\Nova\Fields\Field>
     */
    protected function allFields($fields)
    {
        return (new FieldCollection($fields))->authorized(request());
    }

    /**
     * Assign fields to panels.
     *
     * @return \Illuminate\Support\Collection<int, class-string<\Laravel\Nova\Tool>>
     */
    protected function assignToPanels($label, FieldCollection $fields)
    {
        return $fields->map(function ($field) use ($label) {
            if (!$field->panel) $field->panel = $label;
            return $field;
        });
    }

    /**
     * Return the panels for this request with the default label.
     *
     * @param  string  $label
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    protected function panelsWithDefaultLabel($label, $fields)
    {
        return with(
            collect($fields)->whereInstanceOf(Panel::class)->unique('name')->values(),
            function ($panels) use ($label) {
                return $panels->when($panels->where('name', $label)->isEmpty(), function ($panels) use ($label) {
                    return $panels->prepend((new Panel($label))->withToolbar());
                })->all();
            }
        );
    }

    /**
     * Return the base collection of Nova tool.
     *
     * @return \Illuminate\Support\Collection<int, class-string<\Laravel\Nova\Resource>>
     */
	protected function toolCollection($request)
	{
		return Collection::make(Nova::availableTools($request));
	}

    /**
     * Get the tool class name for a given key.
     *
     * @param  string  $key
     * @return class-string<\Laravel\Nova\Tool>|null
     */
	protected function toolForKey($request, $key)
	{
        return $this->toolCollection($request)->first(function ($value) use ($key) {
            return method_exists($value, 'uriKey') && $value::uriKey() === $key;
        });
	}

    /**
     * Unauthorized
     *
     * @return json response
     */
    protected function unauthorized()
    {
        return abort(403);
    }
}