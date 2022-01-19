<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Update101 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('m_aegis_projects', function (Blueprint $table) {
            $table->foreignId('company_id')->after('type_id')->nullable()->references('id')->on('m_aegis_companies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('m_aegis_projects', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
        });
    }
}
