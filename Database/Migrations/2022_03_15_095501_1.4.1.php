<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('m_aegis_competency_details', 'm_aegis_competency_details');
        Schema::table('m_aegis_competency_details', function (Blueprint $table){
            $table->text('live_document')->nullable()->after('company_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('m_aegis_competency_details', 'm_aegis_competency_details');
        Schema::table('m_aegis_competency_details', function (Blueprint $table){
            $table->dropColumn('live_document');
        });
    }
};
