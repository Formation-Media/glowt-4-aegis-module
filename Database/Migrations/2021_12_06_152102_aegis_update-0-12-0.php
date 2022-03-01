<?php

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\AEGIS\Models\Company;

class AegisUpdate0120 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('m_aegis_companies', function (Blueprint $table) {
            $table->string('abbreviation', 3)->after('id');
        });
        $company_abbreviations = [];
        $user_abbreviations    = [];
        if ($companies = Company::withTrashed()->get()) {
            foreach ($companies as $company) {
                $abbreviation = $this->company_abbreviation($company->name);
                $loop         = 1;
                while (in_array($abbreviation, $company_abbreviations)) {
                    $abbreviation = $this->company_abbreviation($company->name, $loop);
                    $loop++;
                }
                $company_abbreviations[] = $abbreviation;
                $company->abbreviation   = $abbreviation;
                $company->save();
            }
        }
        if ($users = User::withTrashed()->get()) {
            foreach ($users as $user) {
                $base_abbreviation = substr($user->first_name, 0, 1).substr($user->last_name, 0, 1);
                $loop              = 1;
                $abbreviation      = strtoupper($base_abbreviation.$loop);
                while (in_array($abbreviation, $user_abbreviations)) {
                    $abbreviation = strtoupper($base_abbreviation.$loop++);
                }
                $user_abbreviations[] = $abbreviation;
                $user->setMeta('aegis.user-reference', $abbreviation);
                $user->save();
            }
        }
        Schema::create('m_aegis_document_approval_item_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_item_id')->references('id')->on('m_documents_documents_approval_items')->onDelete('cascade');
            $table->foreignId('company_id')->references('id')->on('m_aegis_companies')->onDelete('cascade');
            $table->foreignId('job_title_id')->references('id')->on('m_aegis_job_titles')->nullable();
            $table->timestamps();
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
        Schema::table('m_aegis_companies', function (Blueprint $table) {
            $table->dropColumn('abbreviation');
        });
        \DB::table('users_meta')->where('key', 'aegis.user-reference')->delete();
        Schema::dropIfExists('m_aegis_document_approval_item_details');
    }
    private function company_abbreviation($company, $loop = 1)
    {
        $return = '';
        if (strlen($company) <= 3) {
            $return = $company;
        }
        $words = explode(' ', $company);
        if (count($words) === 1) {
            $return = substr($words[0], 0, 3);
        } elseif (count($words) === 2) {
            $return = substr($words[0], 0, 1).substr($words[1], 0, 2);
        } else {
            $return = substr($words[0], 0, 1).substr($words[1], 0, 1).substr($words[2], $loop - 1, $loop);
        }
        return strtoupper($return);
    }
}
