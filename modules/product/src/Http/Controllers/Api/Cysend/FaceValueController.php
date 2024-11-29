<?php

namespace App\Product\Http\Controllers\Api\Cysend;

use App\Core\Controller;
use Illuminate\Http\Request;
use App\Vendor\Services\VendorService;
use App\Product\Entities\FaceValueApi;

class FaceValueController extends Controller
{
    public function getFaceValues(Request $request)
    {
        // $json = json_decode(file_get_contents(str_replace('//', '/', module_path('product', 'faceValues.json'))), true);


        $service = new VendorService('cysend');
        $values = $service->getFaceValues();

        foreach(collect($values->fixed)->chunk(200)->toArray() as $group) {

            $bulk = FaceValueApi::query()->bulk();

            $bulk->uniqueBy(['face_value_id'])
                ->upsert(
                    collect($group)->map(fn($faceValue): array => [
                        'vendor_id' => 2,
                        'product_id' => $faceValue->product_id,
                        'face_value_id' => "cysend-{$faceValue->face_value_id}",
                        'type' => 'fixed',
                        'face_value_currency' => $faceValue->face_value_currency,
                        'face_value' => $faceValue->face_value,
                        'definition' => $faceValue->definition,
                        'cost_currency' => $faceValue->cost_currency,
                        'cost' => $faceValue->cost,
                        'promotion' => $faceValue->promotion,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])->toArray()
                );

            // \DB::table('face_value_apis')->upsert(
            //     collect($group)->map(fn($faceValue): array => [
            //         'vendor_id' => 2,
            //         'product_id' => $faceValue->product_id,
            //         'face_value_id' => "cysend-{$faceValue->face_value_id}",
            //         'type' => 'fixed',
            //         'face_value_currency' => $faceValue->face_value_currency,
            //         'face_value' => $faceValue->face_value,
            //         'definition' => $faceValue->definition,
            //         'cost_currency' => $faceValue->cost_currency,
            //         'cost' => $faceValue->cost,
            //         'promotion' => $faceValue->promotion,
            //         'created_at' => now(),
            //         'updated_at' => now(),
            //     ])->toArray(),
            //     'face_value_id',
            //     ['face_value_currency', 'face_value', 'definition', 'cost_currency', 'cost', 'promotion', 'updated_at']
            // );

            // \DB::table('face_value_apis')->insert(
            //     collect($group)->map(fn($faceValue): array => [
            //         'vendor_id' => 2,
            //         'product_id' => $faceValue->product_id,
            //         'face_value_id' => $faceValue->face_value_id,
            //         'type' => 'fixed',
            //         'face_value_currency' => $faceValue->face_value_currency,
            //         'face_value' => $faceValue->face_value,
            //         'definition' => $faceValue->definition,
            //         'cost_currency' => $faceValue->cost_currency,
            //         'cost' => $faceValue->cost,
            //         'promotion' => $faceValue->promotion,
            //         'created_at' => now(),
            //         'updated_at' => now(),
            //     ])->toArray()
            // );
        }

        foreach(collect($values->range)->chunk(200)->toArray() as $group) {

            $bulk = FaceValueApi::query()->bulk();

            $bulk->uniqueBy(['face_value_id'])
                ->upsert(
                    collect($group)->map(fn($faceValue): array => [
                        'vendor_id' => 2,
                        'product_id' => $faceValue->product_id,
                        'face_value_id' => "cysend-{$faceValue->face_value_id}",
                        'type' => 'range',
                        'face_value_currency' => $faceValue->face_value_currency,
                        'face_value' => $faceValue->face_value_from,
                        'max_face_value' => $faceValue->face_value_to,
                        'face_value_step' => $faceValue->face_value_step,
                        'cost_currency' => $faceValue->cost_currency,
                        'cost' => $faceValue->minimum_cost,
                        'max_cost' => $faceValue->maximum_cost,
                        'promotion' => $faceValue->promotion,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])->toArray()
                );

            // \DB::table('face_value_apis')->upsert(
            //     collect($group)->map(fn($faceValue): array => [
            //         'vendor_id' => 2,
            //         'product_id' => $faceValue->product_id,
            //         'face_value_id' => "cysend-{$faceValue->face_value_id}",
            //         'type' => 'range',
            //         'face_value_currency' => $faceValue->face_value_currency,
            //         'face_value' => $faceValue->face_value_from,
            //         'max_face_value' => $faceValue->face_value_to,
            //         'face_value_step' => $faceValue->face_value_step,
            //         'cost_currency' => $faceValue->cost_currency,
            //         'cost' => $faceValue->minimum_cost,
            //         'max_cost' => $faceValue->maximum_cost,
            //         'promotion' => $faceValue->promotion,
            //         'created_at' => now(),
            //         'updated_at' => now(),
            //     ])->toArray(),
            //     'face_value_id',
            //     ['face_value_currency', 'face_value', 'max_face_value', 'face_value_step', 'definition', 'cost_currency', 'cost', 'max_cost', 'promotion', 'updated_at']
            // );

            // \DB::table('face_value_apis')->insert(
            //     collect($group)->map(fn($faceValue): array => [
            //         'vendor_id' => 2,
            //         'product_id' => $faceValue->product_id,
            //         'face_value_id' => $faceValue->face_value_id,
            //         'type' => 'range',
            //         'face_value_currency' => $faceValue->face_value_currency,
            //         'face_value' => $faceValue->face_value_from,
            //         'max_face_value' => $faceValue->face_value_to,
            //         'face_value_step' => $faceValue->face_value_step,
            //         'cost_currency' => $faceValue->cost_currency,
            //         'cost' => $faceValue->minimum_cost,
            //         'max_cost' => $faceValue->maximum_cost,
            //         'promotion' => $faceValue->promotion,
            //         'created_at' => now(),
            //         'updated_at' => now(),
            //     ])->toArray()
            // );
        }

        return json_response([], 200);
    }
}
