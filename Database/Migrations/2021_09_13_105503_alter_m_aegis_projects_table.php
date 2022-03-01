<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterMAegisProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('m_aegis_projects', function (Blueprint $table){
            $table->text('description');
            $table->string('reference')->unique();
        });

        Schema::table('m_aegis_project_variants', function (Blueprint $table){
            $table->string('reference')->unique();
            $table->integer('variant_number');
        });

        Schema::table('m_aegis_variant_documents', function (Blueprint $table){
            $table->string('reference')->unique();
        });

        Schema::table('m_aegis_scopes', function (Blueprint $table){
            $table->string('reference')->unique();
        });
        Schema::enableForeignKeyConstraints();
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
