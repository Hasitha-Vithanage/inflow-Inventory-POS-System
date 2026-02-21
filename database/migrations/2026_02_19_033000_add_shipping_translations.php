<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddShippingTranslations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $translations = [
            ['locale' => 'en', 'key' => 'ShippingCompanies', 'value' => 'Shipping Companies'],
            ['locale' => 'en', 'key' => 'ShippingMethods', 'value' => 'Shipping Methods'],
            ['locale' => 'en', 'key' => 'DefaultShippingMethod', 'value' => 'Default Shipping Method'],
            ['locale' => 'en', 'key' => 'Choose_Shipping_Method', 'value' => 'Choose Shipping Method'],
        ];

        foreach ($translations as $translation) {
            DB::table('translations')->updateOrInsert(
                ['locale' => $translation['locale'], 'key' => $translation['key']],
                ['value' => $translation['value']]
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
