<?php

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Exception\ParseException;
use App\Core\Exceptions\ModuleNotFoundException;
use Illuminate\Support\Facades\{Schema, DB};

if (!function_exists('modules')) {
    /**
     * Get modules repository.
     *
     * @param string $location
     * @return \App\Core\RepositoryManager \App\Core\Repository
     */
    function modules($location = null) {
        if ($location) {
            return app('modules')->location($location);
        }

        return app('modules');
    }
}

if (!function_exists('module_path')) {
    /**
     * Return the path to the given module file.
     *
     * @param string $slug
     * @param string $file
     *
     * @param string|null $location
     * @return string
     * @throws \App\Core\Exceptions\ModuleNotFoundException
     */
    function module_path($slug = null, $file = '', $location = null , $inSrc = false , $typeSeeder = false)
    {
        $location = $location ?: "modules";
        $modulesPath = base_path('modules');


        $filePath = $file ? '/' . ltrim($file, '/') : '';

        if (is_null($slug)) {
            if (empty($file)) {
                return $modulesPath;
            }
            return $modulesPath . $filePath;
        }


        $module = Module::location($location)->where('slug', $slug);

        if (is_null($module)) {
            throw new ModuleNotFoundException($slug);
        }

        if ($inSrc) {
            return $modulesPath . '/' . $module['basename'] . '/src' . $filePath;
        }

        else if ($typeSeeder) {
            return $modulesPath . '/' . $module['basename'] . $filePath;
        }

        else{
            return $modulesPath . '/' . $module['basename'] . '/' . $filePath;
        }

    }
}


if (!function_exists('module_class')) {
    /**
     * Return the full path to the given module class.
     *
     * @param string $slug
     * @param string $class
     * @param string $location
     * @return string
     * @throws \App\Core\Exceptions\ModuleNotFoundException
     */
    function module_class($slug, $class, $location = null)
    {
        $location = $location ?: base_path('modules');
        $module = modules($location)->where('slug', $slug);


        if (is_null($module)) {
            throw new ModuleNotFoundException($slug);
        }

        $namespace = "App\\" . $module['name'];

        return "$namespace\\$class";
    }
}

if (!function_exists('getResources')) {


    /**
     * Autoload all resources of your module.
     *
     * @return array
     */
    function getResources($resourcesPath, $namSpace)
    {
        $resources = [];
        foreach ((new Finder)->in($resourcesPath)->files() as $resource) {
            $fileName = str_replace('.php', '', $resource->getFilename());
            $resources[] = str_replace('/' , '\\' ,"{$namSpace}/Resources/{$fileName}");
        }
        return $resources;
    }
}

if (!function_exists('check_yaml')) {

    function check_yaml($value)
    {
        try {
            $result = Yaml::parse($value);
        } catch (ParseException $exception) {
            printf("Unable to parse the YAML string: %s \n Error line : \n %s", $exception->getMessage(), $exception->getSnippet());
        }
        return $result;
    }
}

if (!function_exists('check_override_method')) {
    /**
     * Check all method in classes if the method is override on parent
     *
     * @param $class
     * @param $method
     * @return bool
     * @throws ReflectionException
     */
    function check_override_method($class, $method)
    {
        $reflectionClass = new ReflectionClass($class);
        if ($reflectionClass->hasMethod($method)) {
            $reflectionMethod = new \ReflectionMethod($class, $method);
            if ($reflectionMethod->getDeclaringClass()->getName() === $class) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('json_response')) {
    /**
     * Response controller
     *
     * @param array $data
     * @param int   $status_code
     * @return \Illuminate\Http\JsonResponse
     */
    function json_response(array $data = [], $status_code = 201)
    {
        return response()->json($data, $status_code);
    }
}

if (!function_exists('dropIfExistsWithoutRelation')) {
    /**
     * dropIfExistsWithoutRelation
     *
     * @param $tableName
     * @void
     */
    function dropIfExistsWithoutRelation($tableName)
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists($tableName);
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}

if (! function_exists('checkFilledJsonColumn')) {

    /**
     * checkFilledJsonColumn
     *
     * @param $value
     * @void
     */
    function checkFilledJsonColumn($value)
    {
        $fill = false;

        if ($value && $value != 'null') :
            foreach(array_keys(config('translatable.locales')) as $lang):
                if(isset($value[$lang]) && $value[$lang] && $value[$lang] != 'null'):
                    $fill = true;
                    break;
                endif;
            endforeach;
        endif;

        return $fill;
    }
}



if (! function_exists('getLocalesKey')) {

    /**
     * getLocalesKey
     *
     * @param $value
     * @void
     */
    function getLocalesKey()
    {
        return array_keys(config('translatable.locales'));
    }
}

if (! function_exists('paginate')) {
    function paginate($items, $perPage)
    {
        if ($perPage == null || $perPage == 0) {
            $perPage = 500;
        }

        $pageStart = request('page', 1);
        $offSet    = ($pageStart * $perPage) - $perPage;
        $itemsForCurrentPage = $items->slice($offSet, $perPage);
        return new Illuminate\Pagination\LengthAwarePaginator(
            $itemsForCurrentPage, $items->count(), $perPage,
            Illuminate\Pagination\Paginator::resolveCurrentPage(),
            ['path' => Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );
    }
}

if (! function_exists('faTOen')) {
    function faTOen($string) {
        return strtr($string, array('۰'=>'0', '۱'=>'1', '۲'=>'2', '۳'=>'3', '۴'=>'4', '۵'=>'5', '۶'=>'6', '۷'=>'7', '۸'=>'8', '۹'=>'9', '٠'=>'0', '١'=>'1', '٢'=>'2', '٣'=>'3', '٤'=>'4', '٥'=>'5', '٦'=>'6', '٧'=>'7', '٨'=>'8', '٩'=>'9'));
    }
}

if (! function_exists('slugify')) {
    function slugify($text, $delimiter = '-') {
        return \Illuminate\Support\Str::slug($text, $delimiter, null);
    }
}