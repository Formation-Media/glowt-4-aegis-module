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
        $base_path  = 'modules/aegis/signatures/';
        $db         = [];
        $signatures = File::where('fileable_type', User::class);
        if ($signatures->count() > 0) {
            foreach ($signatures->get() as $signature) {
                if ($signature->fileable) {
                    $db_entry = $signature->toArray();
                    $db_entry['fileable_id']       = null;
                    $db_entry['fileable_id_email'] = $signature->fileable->email;
                    $db_entry['path']              = $signature->getRawOriginal('path');
                    unset(
                        $db_entry['fileable'],
                        $db_entry['id']
                    );

                    $db[]      = $db_entry;
                    $path      = $base_path.str_replace('files/', '', $signature->path);
                    $directory = dirname($path);
                    \Storage::makeDirectory($directory);
                    if ($signature->version === '2a') {
                        \Debug::debug();
                    } else {
                        // Original
                        \Storage::copy($signature->path, $path);
                        // Thumbnail
                        \Storage::copy(
                            str_replace('original.png', 'thumb.png', $signature->path),
                            str_replace('original.png', 'thumb.png', $path)
                        );
                        // Medium
                        \Storage::copy(
                            str_replace('original.png', 'medium.png', $signature->path),
                            str_replace('original.png', 'medium.png', $path)
                        );
                    }
                }
            }
        }
        \Storage::put($base_path.'db.json', json_encode($db, JSON_PRETTY_PRINT));
        $this->info('Finished exporting signatures to `/storage/'.$base_path.'`');
    }
}
