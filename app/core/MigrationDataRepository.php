<?php

namespace App\Core;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{Schema , DB , Hash};
use App\User\Entities\{Permission , Role};
use Symfony\Component\Yaml\Yaml;

class MigrationDataRepository
{

    /**
     * All permissions List
     *
     * @return Array
     */
    private $YamlFiles;

    /**
     * check permissionss.
     *
     * @return \Illuminate\Support\Collection
     */
    public function checkPermissions($permissions)
    {   
        $slugs = [];
        $permissionsList = [];

        foreach ($permissions as $p) {
            if (! empty($p)) {
                if (isset($p['permissions'])) {
                    foreach($p['permissions'] as $permission)
                    {
                        $permissionsList[] = $permission;
                    }
                }
            }
        }

        foreach (collect($permissionsList)->sortBy('order') as $permissionss) {
            if (! isset($permissionss['active']) || $permissionss['active'] != false) {
                $key = $permissionss['name'];
                foreach ($permissionss['actions'] as $permission) {
                    $makeSlug = "{$permission}.{$key}";
                    $slugs[] = $makeSlug;
                    $this->createOrIgnorePermissionSlug($key , $permission);
                    
                }
            }
        }

        $this->deleteNotFoundPermissionSlug($slugs);
    }

    /**
     * check roles.
     *
     * @return \Illuminate\Support\Collection
     */
    public function checkRoles($rolesList)
    {   
        $slugs = [];
        foreach ($rolesList as $roles) {
            if (! empty($roles)) {
                if (isset($roles['roles'])) {
                    foreach ($roles['roles'] as $role) {
                        if (! isset($role['active']) || $role['active'] != false) {
                            $this->createOrIgnoreRoleSlug($role);
                        }
                    }
                }
            }
        }
    }

    /**
     * check data.
     *
     * @return \Illuminate\Support\Collection
     */
    public function checkData($dataList)
    {   
        $data = [];
        foreach ($dataList as $datas) {
            if (! empty($datas)) {
                if (isset($datas['data'])) {
                    foreach ($datas['data'] as $dt) {
                        $model = null;
                        $modelExists = false;
                        if (isset($dt['findBy']) && $dt['findBy']) {
                            $nameSpcae = $dt['modelNameSpace'];
                            $model = $nameSpcae::where($dt['findBy']['field'] , $dt['findBy']['value'])->first();
                            if ($model) {
                                $modelExists = true;
                            }
                        }
                        if(! $modelExists){
                            $model = new $dt['modelNameSpace'];
                            if (isset($dt['data']) && count($dt['data'])) {
                                $uniq = false;
                                foreach ($dt['data'] as $dataModel) {
                                    if (isset($dataModel['isUnique']) && $dataModel['isUnique'] == true) {
                                        $nameSpcae = $dt['modelNameSpace'];
                                        if($nameSpcae::where($dataModel['field'] , $dataModel['value'])->exists()){
                                             $uniq = true;
                                        }
                                        else{
                                            $model->{$dataModel['field']} = $dataModel['value'];
                                        }
                                    }
                                    else{
                                        if (isset($dataModel['hash']) && $dataModel['hash'] == true) {
                                            $model->{$dataModel['field']} = Hash::make($dataModel['value']);
                                        }
                                        else if(isset($dataModel['isJson']) && $dataModel['isJson'] == true)
                                        {
                                            $model->{$dataModel['field']} = str_replace("'", '"', $dataModel['value']);
                                        }                                        
                                        else if(isset($dataModel['multiLang']) && $dataModel['multiLang'] == true)
                                        {
                                            $localesWithValueList = [];
                                            foreach ($dataModel['locales'] as $key => $locale) {
                                                if (isset($dataModel["{$locale}Value"])) {
                                                    $localesWithValueList[$locale] = $dataModel["{$locale}Value"];
                                                } else {
                                                    $localesWithValueList[$locale] = null;
                                                }
                                            }
                                            $model->setTranslations($dataModel['field'], $localesWithValueList);
                                        }
                                        else{
                                            $model->{$dataModel['field']} = $dataModel['value'];
                                        }
                                    }
                                }
                                if (! $uniq) {
                                    $model->save();
                                }
                            }
                        }

                        if (isset($dt['isUser']) && $dt['isUser'] == true) {
                            if (isset($dt['roles']) && count($dt['roles'])) {
                                $rolesId = [];
                                foreach (Role::whereIn('slug' , $dt['roles'])->get() as $role) {
                                    $rolesId[] = $role->id;
                                }
                                $model->syncRoles($rolesId);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Get enabled modules and return data
     *
     */
    public function getModules()
    {
        $dir = base_path('modules');

        collect(app('modules')->enabled()->sortBy(['order']))->map(function($item) use($dir){
            $moduleName = $item['slug'];
            $this->getData($dir , $moduleName);
        });

        return $this->YamlFiles;
    }

    /**
     * Get data for certain module
     *
     * @return void
     */
    private function getData($dir , $module)
    {
        if (file_exists("{$dir}/{$module}/data.yaml")) {
            $this->YamlFiles[] = check_yaml(file_get_contents("{$dir}/{$module}/data.yaml"));
        }
    }

    /**
     * Create or ignore permission slug
     *
     * @return void
     */
    private function createOrIgnorePermissionSlug($key , $value)
    {
        $makeSlug = "{$value}.{$key}";

        if (! Permission::where('slug' , $makeSlug)->exists()) {
            
            switch ($value) {

                case 'view.dashboard':
                    $info = ucfirst($key). ' ' ."Panel";
                    $group = 'Main';
                    break;

                case 'view.any':
                    $info = "See";
                    $group = ucfirst($key);
                    break;

                case 'view':
                    $info = "See Details";
                    $group = ucfirst($key);
                    break;

                case 'create':
                    $info = "Create";
                    $group = ucfirst($key);
                    break;

                case 'update':
                    $info = "Update";
                    $group = ucfirst($key);
                    break;

                case 'delete':
                    $info = "Delete";
                    $group = ucfirst($key);
                    break;

                case 'final.approval':
                    $info = "Final Approval";
                    $group = ucfirst($key);
                    break;

                case 'admin.root':
                    $info = "Admin access";
                    $group = ucfirst($key);
                    break;

                case 'admin.access':
                    $info = "Full Access";
                    $group = ucfirst($key);
                    break;

                case 'freeze':
                    $info = "Can freeze";
                    $group = ucfirst($key);
                    break;

                case 'referral':
                    $info = "Referral";
                    $group = ucfirst($key);
                    break;

                case 'close':
                    $info = "Close";
                    $group = ucfirst($key);
                    break;

                case 'reply':
                    $info = "Reply";
                    $group = ucfirst($key);
                    break;

                case 'actions':
                    $info = "Actions";
                    $group = ucfirst($key);
                    break;

                case 'accreditation':
                    $info = "Accreditation";
                    $group = ucfirst($key);
                    break;

                default:
                    $info = ucfirst($value);
                    $group = $key;
                    break;
            }

            if (Schema::hasTable('permissions')) {
                  DB::table('permissions')->insert(
                        [
                            [
                                'name' => $info,
                                'slug' => $value. '.' .$key,
                                'group' => $group,
                                'description' => $info,
                                'model' => 'permission',
                            ]
                        ]
                 );
             }
        }
    }

    /**
     * Create or ignore Role slug
     *
     * @return void
     */
    private function createOrIgnoreRoleSlug($role)
    {
        $roleName = $role['name'];
        $roleSlug = Str::slug($roleName, '.');
        $roleModel = Role::where('slug' , $roleSlug)->first();
  
        if (! $roleModel) {
            $roleModel = $this->createRole($roleName , $roleSlug);
        } if (isset($role['allPermissions']) && $role['allPermissions'] == true) {
            $exceptlist = [];
            if (isset($role['except'])) {
                $exceptlist = count($role['except']) ? $role['except'] : [];
            }
            $this->syncPermissions($roleModel , [] , true , $exceptlist);
        } else if (isset($role['getPermissions']) && count($role['getPermissions'])) {
            $this->syncPermissions($roleModel , $role['getPermissions']);
        } else
            return;
    }

    /**
     * Create Role
     *
     * @return id
     */
    private function createRole($roleName , $roleSlug)
    {
        $newRole = new Role;
        $newRole->name = $roleName;
        $newRole->slug = $roleSlug;
        $newRole->save();
        return $newRole;
    }

    /**
     * Sync Permissions
     *
     * @return void
     */
    private function syncPermissions($roleModel , $permissions , $allPermissions = false , $exceptlist = [])
    {
        if ($allPermissions) {
            if (Schema::hasTable('permission_role')) {
                $permitted = [];
                foreach (Permission::whereNotIn('slug' , $exceptlist)->get() as $permission) {
                    $permitted[] = $permission->id;
                }
                $roleModel->syncPermissions($permitted);
            }
        } else{
            $permitted = [];

            foreach ($permissions as $permission) {
                if (isset($permission['allActions']) && $permission['allActions'] == true) {
                    foreach (Permission::where('group' , ucfirst($permission['groupName']))->get() as $per) {
                        $permitted[] = $per->id;
                    }
                } else if(isset($permission['actions']) && count($permission['actions'])){
                    $permissionGroup = [];
                    foreach ($permission['actions'] as $action) {
                
                        $permissionGroup[] = $action. '.' . $permission['groupName'];
                    }

                    foreach (Permission::whereIn('slug' , $permissionGroup)->get() as $per) {
                        $permitted[] = $per->id;
                    }
                }
            }
            $roleModel->syncPermissions($permitted);
        }
    }

    /**
     * Delete not found permission slugs
     *
     * @return void
     */
    private function deleteNotFoundPermissionSlug($slugs)
    {
        $permissions = Permission::whereNotIn('slug' , $slugs)->delete();
    }
}