<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\AEGIS\Models\JobTitle;

class CreateMAegisJobTitles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('m_aegis_job_titles', function (Blueprint $table) {
            $table->id();
            $table->string('name',250);
            $table->boolean('status');
            $table->timestamps();
        });
        if($job_titles=\DB::table('users_meta')->select('value')->where('key','aegis.discipline')->distinct()->get()){
            foreach($job_titles as $job_title){
                JobTitle::create([
                    'name'  =>$job_title->value,
                    'status'=>true,
                ]);
            }
            $job_titles=JobTitle::all();
            foreach($job_titles as $job_title){
                \DB::table('users_meta')->where('key','aegis.discipline')->update(['value'=>$job_title->id]);
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
        Schema::dropIfExists('m_aegis_job_titles');
    }
}
