<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameSuppliersToCompanies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('m_aegis_suppliers','m_aegis_companies');
        Schema::rename('m_aegis_competency_supplier','m_aegis_competency_company');
        Schema::table('m_aegis_competency_company', function (Blueprint $table) {
            $table->renameColumn('supplier_id', 'company_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('m_aegis_competency_company', function (Blueprint $table) {
            $table->renameColumn('company_id', 'supplier_id');
        });
        Schema::rename('m_aegis_competency_company','m_aegis_competency_supplier');
        Schema::rename('m_aegis_companies','m_aegis_suppliers');
    }
}
