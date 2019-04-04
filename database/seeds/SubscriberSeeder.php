<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class SubscriberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call('DefaultPopulator');

        Model::reguard();
    }
}

/**
 * Class DefaultPopulator
 */
class DefaultPopulator extends Seeder
{
    public function run()
    {
        $ApiSubscriber = App\Api\V1\Models\ApiSubscriber::create(
            [
                'username' => 'test@user.dev',
                'password' => 'secret',
                'userable_id' => 1,
                'userable_type' => 'device'
            ]
        );

        $ApiSubscriber = App\Api\V1\Models\ApiSubscriber::create(
            [
                'username' => '20c9d086c557',
                'password' => '20c9d086c557',
                'userable_id' => 2,
                'userable_type' => 'device'
            ]
        );

        $ApiSubscriber = App\Api\V1\Models\ApiSubscriber::create(
            [
                'username' => 'solo.admin',
                'password' => '12345',
                'userable_id' => 1,
                'userable_type' => 'employee'
            ]
        );

        $ApiSubscriber = App\Api\V1\Models\ApiSubscriber::create(
            [
                'username' => 'igul',
                'password' => 'igul12345',
                'userable_id' => 2,
                'userable_type' => 'employee'
            ]
        );





        DB::table('employees')->insert([
            'username' => 'solo.admin',
            'first_name' => 'Solo',
            'last_name' => 'Admin',
            'email' => 'solo@skylinedynamics.com',
            'mobile' => '966512345678',
            'status' => 'active'
        ]);

        DB::table('employees')->insert([
            'username' => 'igul',
            'first_name' => 'Ihsan',
            'last_name' => 'Gul',
            'email' => 'igul@foodbasics.co',
            'mobile' => '966512345678',
            'status' => 'active'
        ]);

        DB::table('concept_employee')->insert([
            'concept_id' => 2,
            'employee_id' => 1
        ]);

        DB::table('concept_employee')->insert([
            'concept_id' => 1,
            'employee_id' => 2
        ]);

        DB::table('employee_role')->insert([
            'role_id' => 1,
            'employee_id' => 1
        ]);

        DB::table('employee_role')->insert([
            'role_id' => 1,
            'employee_id' => 2
        ]);


        unset($ApiSubscriber);

        return;
    }


}