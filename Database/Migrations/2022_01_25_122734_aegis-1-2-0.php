<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Aegis120 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('m_aegis_feedback_list_types', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('name');
            $table->timestamps();
        });
        Schema::table('m_aegis_variant_documents', function (Blueprint $table){
            $table->integer('issue')->default(1);
        });
        Schema::table('m_aegis_project_variants', function (Blueprint $table) {
            $table->text('description')->after('reference')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('m_aegis_feedback_list_types');
        Schema::table('m_aegis_project_variants', function (Blueprint $table) {
            $table->dropColumn('description');
        });
        Schema::table('m_aegis_variant_documents', function (Blueprint $table){
            $table->dropColumn('issue')->unsigned();
        });
    }
}
