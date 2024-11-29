<?php

namespace App\Core;

use App\Core\Contracts\Repository as RepositoryContract;
use Illuminate\Config\Repository as Config;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Exception;

abstract class Repository implements RepositoryContract
{
    /**
     * @var string
     */
    public $location;

    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * @var string Path to the defined modules directory
     */
    protected $path;

    /**
     * Constructor method.
     *
     * @param string $location
     */
    public function __construct(string $location)
    {
        $this->location = $location;
        $this->files =  new Filesystem;
    }

    /**
     * Get all module basename.
     *
     * @return Collection
     */
    protected function getAllBasename(): Collection
    {
        try {
            $collection = collect($this->files->directories($this->getPath()));

            return $collection->map(function ($item, $key) {
                return basename($item);
            });
        } catch (\InvalidArgumentException $e) {
            return collect([]);
        }
    }

    /**
     * Get a module's manifest contents.
     *
     * @param string $slug
     *
     * @return array|null
     * @throws Exception
     */
    public function getManifest($slug) : ?array
    {
        if (! is_null($slug)) {
            $path = $this->getManifestPath($slug);

            
            try {
                return check_yaml($this->files->get($path));
            } catch(Exception $exception) {
                throw $exception->getMessage();
            }


        }
        return null;
    }

    /**
     * Set modules path in "RunTime" mode.
     *
     * @param string $path
     *
     * @return object $this
     */
    public function setPath(string $path): object
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get modules path.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path ?: base_path('modules');
    }

    /**
     * Get path for the specified module.
     *
     * @param string $slug
     *
     * @return string
     */
    public function getModulePath(string $slug): string
    {
        $module = Str::studly($slug);

        if (\File::exists($this->getPath()."/{$module}/")) {
            return $this->getPath()."/{$module}/";
        }

        return $this->getPath()."/{$slug}/";
    }

    /**
     * Get path of module manifest file.
     *
     * @param $slug
     *
     * @return string
     */
    protected function getManifestPath($slug): string
    {
        return $this->getModulePath($slug)."info.yaml";
    }
}
