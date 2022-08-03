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
        Schema::table('m_aegis_types', function (Blueprint $table){
            $table->foreignId('parent_id')->after('id')->nullable()->references('id')->on('m_documents_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('m_aegis_types', function (Blueprint $table){
            $table->dropForeign(['parent_id']);
        });
    }
};
