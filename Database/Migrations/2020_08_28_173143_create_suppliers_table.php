<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSuppliersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('m_aegis_suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name',250);
            $table->boolean('status');
            $table->timestamps();
        });
        Schema::create('m_aegis_competency_supplier', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('competency_id');
            $table->unsignedBigInteger('supplier_id');
            $table->foreign('competency_id','c_cs_id_foreign')->references('id')->on('m_hr_competencies')->onDelete('cascade');
            $table->foreign('supplier_id',  's_cs_id_foreign')->references('id')->on('m_aegis_suppliers')->onDelete('cascade');
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
        Schema::dropIfExists('suppliers');
    }
}
