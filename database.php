<?php

use App\Services\DB;

require 'vendor/autoload.php';

$db = new DB();

$db->addConnection([
    'driver'   => 'sqlite',
    'database' => ':memory:', // SQLite chạy trong RAM, không cần file vật lý
    'prefix'   => '',
]);

$db->setAsGlobal();
$db->bootEloquent();

// Tạo bảng `forecast_for_race`
DB::schema()->create('forecast_for_race', function ($table) {
    $table->integer('race_id');
    for ($i = 1; $i <= 9; $i++) {
        $table->integer("car_no{$i}_x")->nullable();
        $table->integer("car_no{$i}_y")->nullable();
    }
});
