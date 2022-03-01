<?php

namespace Modules\AEGIS\Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class SeedUserRolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        foreach(array(
            'Project Manager'
        ) as $role){
            $role = Role::firstOrNew(['module' => 'AEGIS', 'name' => $role]);
            $role->save();
        }
    }
}
