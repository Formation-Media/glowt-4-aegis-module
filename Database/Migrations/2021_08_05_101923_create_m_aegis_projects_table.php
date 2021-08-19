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
            $table->foreignId('type_id')->references('id')->on('m_aegis_types');
            $table->foreignId('added_by')->references('id')->on('users');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('m_aegis_project_variants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_default');
            $table->foreignId('project_id')->references('id')->on('m_aegis_projects')->onDelete('cascade');
            $table->foreignId('added_by')->references('id')->on('users');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('m_aegis_variant_documents', function (Blueprint $table){
            $table->id();
            $table->foreignId('variant_id')->references('id')->on('m_aegis_project_variants')->onDelete('cascade');
            $table->foreignId('document_id')->references('id')->on('m_documents_documents')->onDelete('cascade');
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
