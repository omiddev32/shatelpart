<?php

namespace App\Core;

use Illuminate\Foundation\Application as ApplicationSource;
use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\PackageManifest;
use Illuminate\Foundation\ProviderRepository;

class Application extends ApplicationSource
{
    /**
     * The class loader object.
     *
     * @var \Composer\Autoload\ClassLoader
     */
    protected $classLoader;

    /**
     * Create a new Illuminate application instance.
     *
     * @param  string|null  $basePath
     * @return void
     */
    public function __construct($basePath, $classLoader)
    {        
        $this->classLoader = $classLoader;

        if ($basePath) {
            $this->setBasePath($basePath);
        }

        $this->registerBaseBindings();
        $this->registerBaseServiceProviders();
        $this->registerCoreContainerAliases();
    }

    /**
     * Bind all of the application paths in the container.
     *
     * @return void
     */
    protected function bindPathsInContainer()
    {
        $this->instance('path', $this->path());
        $this->instance('path.base', $this->basePath());
        $this->instance('path.config', $this->configPath());
        $this->instance('path.database', $this->databasePath());
        $this->instance('path.public', $this->publicPath());
        $this->instance('path.resources', $this->resourcePath());
        $this->instance('path.storage', $this->storagePath());
        $this->instance('path.lang', $this->langPath());

        $this->useBootstrapPath(value(function () {
            return is_dir($directory = $this->basePath('.laravel'))
                        ? $directory
                        : $this->basePath('bootstrap');
        }));

    }

    /**
     * Get the path to the application configuration files.
     *
     * @param  string  $path
     * @return string
     */
    public function configPath($path = '')
    {

        return $this->basePath.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'config'.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * Get the path to the database directory.
     *
     * @param  string  $path
     * @return string
     */
    public function databasePath($path = '')
    {
        return ($this->databasePath ?: $this->basePath.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'database').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * Get the path to the public / web directory.
     *
     * @return string
     */
    public function publicPath($path = '')
    {
        // return $this->basePath . '/../../public_html/dev';
        return $this->basePath.DIRECTORY_SEPARATOR.'public';
    }

    /**
     * Get the path to the resources directory.
     *
     * @param  string  $path
     * @return string
     */
    public function resourcePath($path = '')
    {
        return $this->joinPaths($this->basePath('templates'), $path);
    }

    /**
     * Get the path to the language files.
     *
     * @param  string  $path
     * @return string
     */
    public function langPath($path = '')
    {
        return $this->basePath().DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'languages';
    }

    /**
     * Get the path to the module directory.
     *
     * @return string
     */
    public function modulePath()
    {
        return $this->basePath().DIRECTORY_SEPARATOR.'modules';
    }

    /**
     * Register all of the configured providers.
     *
     * @return void
     */
    public function registerConfiguredProviders()
    {
        $providers = $this->discoverServiceProviders();

        $providers->splice(1, 0, [$this->make(PackageManifest::class)->providers()]);

        (new ProviderRepository($this, new Filesystem, $this->getCachedServicesPath()))
                    ->load($providers->collapse()->toArray());
    }

    /**
    * Discovers available serviceProviders.
    *
    * @return void
    *   The available serviceProviders.
    */
    public function discoverServiceProviders()
    {
        $this->singleton('modules', function(){
            return new ModulesManager();
        });

        $filenames = $this->getModuleFileNames($this['modules']->load());

        $modulesProviders = [];

        $this->classLoaderAddMultiplePsr4($filenames);

        // Load each module's serviceProvider class.
        foreach ($filenames as $path => $moduleName) {

            $name = "{$moduleName}ServiceProvider";
            $class = "App\\{$moduleName}\\Providers\\{$name}";
            if (class_exists($class)) {
                $modulesProviders[] = $class;
            }
            // $routeClass = "App\\{$module}\\Providers\\RouteServiceProvider";
            // $eventClass = "App\\{$module}\\Providers\\EventServiceProvider";
            // if (class_exists($routeClass)) {
            //     $modulesProviders[] = $routeClass;
            // }
            // if (class_exists($EventClass)) {
            //     $modulesProviders[] = $EventClass;
            // }
        }

        $providers = Collection::make(array_merge($this->make('config')
            ->get('app.providers') , $modulesProviders))
            ->partition(function ($provider) {
                return strpos($provider, 'Illuminate\\') === 0;
            });

        return $providers;
    }

    /**
     * @param array $namespaces
     * @param null $classLoader
     */
    protected function classLoaderAddMultiplePsr4(array $namespaces = [])
    {
        $classLoader = $this->classLoader;

        foreach ($namespaces as $path => $name) {
            $classLoader->addPsr4('App\\' . $name . '\\', $this->modulePath() . '/' .$path . "/src");
        }
    }

    /**
     * Gets the file name for each enabled module.
     *
     * @return array
     */
    protected function getModuleFileNames($repositories)
    {
        $filenames = [];

        foreach ($repositories as $module) {
            $filenames[$module['slug']] = $module['name']; 
        }
        return $filenames;
    }

    /**
     * Get the application namespace.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function getNamespace()
    {
        if (! is_null($this->namespace)) {
            return $this->namespace;
        }

        $composer = json_decode(file_get_contents($this->basePath('composer.json')), true);
        
        foreach ((array) data_get($composer, 'autoload.psr-4') as $namespace => $path) {
            foreach ((array) $path as $pathChoice) {
                if (realpath($this->basePath($pathChoice))) 
                    return $this->namespace = $namespace;
            }
        }

        throw new RuntimeException('Unable to detect application namespace.');
    }
}