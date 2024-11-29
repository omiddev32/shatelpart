<?php

namespace App\Core;

use phpDocumentor\Reflection\Types\Collection;

class ModulesManager
{

    /**
     * @var Location Path
     */
    protected $path;

    /**
     * @var Repository[]
     */
    protected $repositories = [];

    /**
     * Create a new repository manager instance.
     *
     */
    public function __construct()
    {
        $this->path = base_path('modules');
    }

    /**
     * load active modules.
     *
     * @return \Illuminate\Support\Collection
     */
    public function load()
    {
        return $this->repository($this->path)->enabled()->sortBy(['order']);
    }

    /**
     * @return Repository[]
     */
    public function repositories()
    {
        return $this->repositories;
    }

    /**
     * @return Repository for certain location
     */
    public function location($location = null)
    {
        return $this->repository($location);
    }

    /**
     * @param string $location
     * @return LocalRepository
     * @throws \Exception
     */
    protected function repository($location = null)
    {
        return  $this->repositories[$this->path]
            ?? $this->repositories[$this->path] = new LocalRepository($this->path);
    }

    /**
     * Oh sweet sweet magical method.
     *
     * @param string $method
     * @param mixed  $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array([$this->repository(), $method], $arguments);
    }
}
