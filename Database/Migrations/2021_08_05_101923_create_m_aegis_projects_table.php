<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMAegisProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('m_aegis_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('scope_id')->references('id')->on('m_aegis_scopes');
            $table->foreignId('added_by')->references('id')->on('users');
            $table->set('type', ['Engineering', 'HR','Rail']);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('m_aegis_project_variants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_default');
            $table->foreignId('project_id')->references('id')->on('m_aegis_projects');
            $table->foreignId('added_by')->references('id')->on('users');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('m_aegis_variant_documents', function (Blueprint $table){
            $table->id();
            $table->foreignId('variant_id')->references('id')->on('m_aegis_project_variants');
            $table->foreignId('document_id')->references('id')->on('m_dm_documents');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('m_aegis_projects');
        Schema::dropIfExists('m_aegis_project_variants');
        Schema::dropIfExists('m_aegis_variants_documents');
    }
}
