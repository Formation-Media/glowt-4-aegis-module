<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\AEGIS\Models\Company;
use Modules\AEGIS\Models\CompanyType;
use Modules\AEGIS\Models\Type;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('m_aegis_company_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->references('id')->on('m_aegis_companies');
            $table->foreignId('type_id')->references('id')->on('m_aegis_types');
            $table->timestamps();
        });
        if ($types = Type::all()) {
            $companies = Company::all();
            foreach ($types as $type) {
                foreach ($companies as $company) {
                    CompanyType::create([
                        'company_id' => $company->id,
                        'type_id'    => $type->id,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('m_aegis_company_types');
        Schema::enableForeignKeyConstraints();
    }
};
