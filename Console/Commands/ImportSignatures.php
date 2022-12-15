<?php

namespace Modules\AEGIS\Console\Commands;

use App\Models\File;
use App\Models\User;
use Illuminate\Console\Command;

class ImportSignatures extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'signatures:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports user signatures';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Check signatures directory exists');
        $this->info('Loop through db.json to match up email addresses, updating the fileable_id');
        $this->info('Copy files to their directories');
        $this->info('Export list of emails without signatures to .txt');
    }
}
