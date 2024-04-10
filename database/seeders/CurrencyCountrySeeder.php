<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CurrencyCountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Currency::create(['currency'=> 'QAR', 'symbol'=> '﷼']);
        \App\Models\Currency::create(['currency'=> 'USD' , 'symbol'=> '$']);

        \App\Models\Country::create(['country'=> 'QATAR']);
        \App\Models\Country::create(['country'=> 'USA']);
    }
}
