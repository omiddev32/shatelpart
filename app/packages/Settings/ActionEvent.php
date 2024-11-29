<?php

namespace App\Packages\Settings;

use Laravel\Nova\Actions\ActionEvent as ActionModel;
use Illuminate\Support\Str;

class ActionEvent extends ActionModel
{
    /**
     * Create a new action event instance for a settings update.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return static
     */
	public static function forSettingsSave($user, $model)
	{
        return new static([
            'batch_id' => (string) Str::orderedUuid(),
            'user_id' => $user->getAuthIdentifier(),
            'name' => 'Update',
            'actionable_type' => $model->getMorphClass(),
            'actionable_id' => $model->getKey(),
            'target_type' => $model->getMorphClass(),
            'target_id' => $model->getKey(),
            'model_type' => $model->getMorphClass(),
            'model_id' => $model->getKey(),
            'fields' => '',
            'changes' => static::hydrateChangesPayload(
                $changes = array_diff_key($model->getDirty(), array_flip($model->getHidden()))
            ),
            'original' => static::hydrateChangesPayload(
                array_intersect_key($model->newInstance()->setRawAttributes($model->getRawOriginal())->attributesToArray(), $changes)
            ),
            'status' => 'finished',
            'exception' => '',
        ]);
	}
}