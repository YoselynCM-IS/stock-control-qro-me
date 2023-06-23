<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('roles')->insert([
            // 0 => [
            //     'id'        => 1,
            //     'rol'      => 'Administrador',
            //     'created_at' => '2019-06-10 00:00:00',
            //     'updated_at' => '2019-06-10 00:00:00',
            // ],
            // 1 => [
            //     'id'        => 2,
            //     'rol'      => 'Oficina',
            //     'created_at' => '2019-06-10 00:00:00',
            //     'updated_at' => '2019-06-10 00:00:00',
            // ],
            // 2 => [
            //     'id'        => 5,
            //     'rol'      => 'captura'
            // ],
            // 3 => [
            //     'id'        => 6,
            //     'rol'      => 'manager',
            //     'created_at' => '2021-04-29 00:00:00',
            //     'updated_at' => '2021-04-29 00:00:00',
            // ],
            // 4 => [
            //     'id'        => 7,
            //     'rol'      => 'visitor'
            // ]
        ]);

        // \DB::table('users')->insert([
        //     0 => [
        //         'role_id'   => 6,
        //         'name'      => 'Yoselyn', 
        //         'user_name' => 'yoselyn-cm',
        //         'email'     => 'yoselyn@gmail.com',
        //         'password'  => bcrypt('2y053'),
        //         'created_at' => '2021-04-29 00:00:00',
        //         'updated_at' => '2021-04-29 00:00:00',
        //     ],
        // ]);

        \DB::table('users')->insert([
            0 => [
                'role_id'   => 5,
                'name'      => 'Rocio Ceballos', 
                'user_name' => 'rocio-me',
                'email'     => 'rocio@gmail.com',
                'password'  => bcrypt('01c0r63')
            ],
            1 => [
                'role_id'   => 5,
                'name'      => 'Edgar Espinoza', 
                'user_name' => 'edgar-me',
                'email'     => 'edgar@gmail.com',
                'password'  => bcrypt('r46d374')
            ],
        ]);
    }
}
