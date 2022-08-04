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
        Schema::create('m_aegis_training', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->references('id')->on('m_aegis_scopes');
            $table->foreignId('presenter_id')->references('id')->on('users');
            $table->string('name');
            $table->string('reference')->unique();
            $table->string('location');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('duration_length');
            $table->tinyInteger('duration_period');

            $table->string('presentation');
            $table->text('description');

            $table->foreignId('created_by')->references('id')->on('users');
            $table->foreignId('updated_by')->references('id')->on('users');
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
        Schema::disableForeignKeyConstraints();
        Schema::table('m_aegis_types', function (Blueprint $table){
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });
        Schema::enableForeignKeyConstraints();
        Schema::dropIfExists('m_aegis_training');
    }
};
