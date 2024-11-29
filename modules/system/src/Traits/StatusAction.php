<?php

namespace App\System\Traits;

use App\System\Actions\{ActiveStatus, InactiveStatus};

trait StatusAction
{
	public function statusActions($exceptsId = [])
	{
		$status = $this->model()?->status;

		$canRun = true;
		$canSee = true;

		if(count($exceptsId) && $this->id && in_array($this->id, $exceptsId)) {
			$canRun = false;
			$canSee = false;
		}

		return [
			(new ActiveStatus)
				->exceptsId($exceptsId)
				->canSee(fn() => $canSee)
				->canRun(fn() => $canRun)
                ->showInline(! $status)
                ->showOnDetail(! $status)
                ->withName(__('Enable Status')),
			
			(new InactiveStatus)
				->exceptsId($exceptsId)
				->canSee(fn() => $canSee)
				->canRun(fn() => $canRun)
                ->showOnDetail($status)
                ->showInline($status)
                ->withName(__('Disable Status')),
		];
	}
}