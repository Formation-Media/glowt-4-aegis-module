<?php

namespace Modules\AEGIS\Console\Commands;

use App\Models\File;
use App\Models\User;
use Illuminate\Console\Command;

class ExportSignatures extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'signatures:export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exports user signatures';

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
        $db         = [];
        $signatures = File::where('fileable_type', User::class);
        if ($signatures->count() > 0) {
            foreach ($signatures->get() as $signature) {
                if ($signature->fileable) {
                    $db_entry = $signature->toArray();
                    $db_entry['fileable_id']       = null;
                    $db_entry['fileable_id_email'] = $signature->fileable->email;
                    unset($db_entry['fileable']);
                    $db[]                          = $db_entry;
                    $path                          = 'modules/aegis/signatures/'.str_replace('files/', '', $signature->path);
                    $directory                     = dirname($path);
                    \Storage::makeDirectory($directory);
                    if ($signature->version === '2a') {
                        \Debug::debug();
                    } else {
                        \Storage::copy($signature->path, $path);
                    }
                }
            }
        }
        \Storage::put('modules/aegis/signatures/db.json', json_encode($db, JSON_PRETTY_PRINT));
        $this->info('Finished exporting signatures to `/storage/modules/aegis/signatures/`');
    }
}
