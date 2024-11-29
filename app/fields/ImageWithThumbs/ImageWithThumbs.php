<?php

namespace App\Fields\ImageWithThumbs;

use Illuminate\Http\Request;
use Intervention\Image\Facades\Image as Cropper;
use Laravel\Nova\Fields\Image;
use Illuminate\Support\Str;
use Storage;

class ImageWithThumbs extends Image
{
    /**
     * Settings about thumbnail generation.
     *
     * @var array
     */
    public $thumbConfigs = [
        ['name' => '360', 'w' => 360, 'h' => null, 'method' => 'resize'],
        ['name' => '640', 'w' => 640, 'h' => null, 'method' => 'resize'],
        ['name' => '1024', 'w' => 1024, 'h' => null, 'method' => 'resize'],
    ];

    public function __construct($name, $attribute = null, $disk = 'public', $storageCallback = null)
    {
        parent::__construct($name, $attribute, $disk, $storageCallback);

        $this->store(function (Request $request, $model, $attribute, $requestAttribute) {
            return $this->storeWithThumbs($request, $model, $attribute, $requestAttribute);
        })->delete(function (Request $request, $model, $disk, $path) {
            return $this->deleteWithThumbs($request, $model, $disk, $path);
        });

    }

    public function thumbs(array $thumbConfigs)
    {
        $this->validateThumbConfigs($thumbConfigs);
        $this->thumbConfigs = $thumbConfigs;
        return $this;
    }

    private function validateThumbConfigs($configs)
    {
        collect($configs)->each(function ($thumbConfig) {
            if (!isset($thumbConfig['name'])) {
                throw new \InvalidArgumentException("Name attribute is mandatory in thumbConfigs for $this->attribute NovaImageWithThumbs Field");
            }
            if (!isset($thumbConfig['w']) || !is_numeric($thumbConfig['w'])) {
                throw new \InvalidArgumentException("Width attribute must be numeric in thumbConfigs for $this->attribute NovaImageWithThumbs Field");
            }
            // if (!isset($thumbConfig['h']) || !is_numeric($thumbConfig['h'])) {
            //     throw new \InvalidArgumentException("Height attribute must be numeric in thumbConfigs for $this->attribute NovaImageWithThumbs Field");
            // }
            if (!isset($thumbConfig['h']) || !in_array($thumbConfig['method'], ['fit', 'resize'])) {
                throw new \InvalidArgumentException("Height attribute must be one between 'fit' or 'resize' in thumbConfigs for $this->attribute NovaImageWithThumbs Field");
            }
        });
    }

    private function storeWithThumbs($request, $model, $attribute, $requestAttribute)
    {
        $original = $this->storeFile($request, $requestAttribute);
        $originalExtension = pathinfo($original, PATHINFO_EXTENSION);
        $originalFilename = pathinfo($original, PATHINFO_FILENAME);
        $originalFilename = str_replace(".{$originalExtension}", '', $originalFilename);

        $index = 0;

        return collect($this->thumbConfigs)->reduce(function ($all, $config) use ($request, $model, $requestAttribute, $originalFilename, $originalExtension, &$index) {
            $fileName = $this->getStorageDir() . DIRECTORY_SEPARATOR . $originalFilename . '-' . $config['name'] . '.' . $originalExtension;
            $method = $config['method'];
            $name = $config['name'];
            $imageThumb = Cropper::make($request->{$requestAttribute})->$method(
                $config['w'],
                (is_numeric($config['h']) ? $config['h'] : null),
                function ($constraint) use($config){
                    $constraint->aspectRatio();
                    if(is_numeric($config['h'])) {
                        $c->upsize();
                    }
                })->encode($originalExtension, 90);
            Storage::disk($this->getStorageDisk())->put($fileName, (string) $imageThumb);

            // $all[$name] = $fileName;


            if ($this->isPrunable()) {
                $imageData = $this->getProductImageName($model->$name);
                $oldName = $imageData['name'] . '-'. $config['name'] .'.' . $imageData['format'];
                Storage::disk($this->getStorageDisk())->delete($oldName);
            }

            return $all;
        }, [$attribute => $original]);

    }

    /**
     * Get Product Image
     *
     * @param string $data nullable
     * @return string
     */
    protected function getProductImage($image = '')
    {
        return Storage::disk('products')->url($image ?: 'Sharjit-Gift-Card-Template.png');
    }

    /**
     * Get Product Image Name
     *
     * @param string $image
     * @return string
     */
    protected function getProductImageName($image)
    {
        if(Str::of($image)->endsWith('.png')) {
            return [
                'format' => 'png',
                'name' => Str::before($image, '.png'),
            ];
        } else if(Str::of($image)->endsWith('.jpg')) {
            return [
                'format' => 'jpg',
                'name' => Str::before($image, '.jpg'),
            ];
        } else if(Str::of($image)->endsWith('.jpeg')) {
            return [
                'format' => 'jpeg',
                'name' => Str::before($image, '.jpeg'),
            ];
        } else if(Str::of($image)->endsWith('.gif')) {
            return [
                'format' => 'gif',
                'name' => Str::before($image, '.gif'),
            ];
        } else {
            return [
                'format' => 'svg',
                'name' => Str::before($image, '.svg'),
            ];
        }
    }

    private function deleteWithThumbs($request, $model, $disk, $path)
    {

        if (!$this->isPrunable()) {
            return;
        }

        if (!$path) {
            return;
        }

        Storage::disk($this->getStorageDisk())->delete($path);

        return collect($this->thumbConfigs)->reduce(function ($all, $config) use ($model) {
            $name = $config['name'];
            if (!$model->name) {
                return $all;
            }

            Storage::disk($this->getStorageDisk())->delete($model->name);

            $all[$name] = null;

            return $all;
        }, [$this->attribute => null]);
    }

}