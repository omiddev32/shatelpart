<?php

namespace App\System\Traits;

use App\Fields\PersianDate\PersianDateTime;

trait TimestampsField
{
	public function createdAt()
	{
		return PersianDateTime::make(__("Created At"), 'created_at')
                ->color('rgb(30, 136, 229)')
                ->format('jYYYY/jMM/jDD HH:mm:ss')
                ->exceptOnForms();
	}	

	public function updatedAt()
	{
		return PersianDateTime::make(__("Updated At"), 'updated_at')
                ->color('rgb(30, 136, 229)')
                ->format('jYYYY/jMM/jDD HH:mm:ss')
                ->exceptOnForms();
	}

	public function deletedAt()
	{
		return PersianDateTime::make(__("Deleted At"), 'deleted_at')
                ->color('rgb(30, 136, 229)')
                ->format('jYYYY/jMM/jDD HH:mm:ss')
                ->exceptOnForms();
	}

	public function timestamp($name, $attribute)
	{
		return PersianDateTime::make($name, $attribute)
                ->color('rgb(30, 136, 229)')
                ->format('jYYYY/jMM/jDD HH:mm:ss')
                ->exceptOnForms();
	}
}