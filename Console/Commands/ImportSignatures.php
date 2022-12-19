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
        $signature_path = 'modules/aegis/signatures/';

        $directories = \Storage::directories($signature_path);
        $errors      = 0;
        $log_file    = $signature_path.'log.md';

        if ($directories) {
            \Storage::put($log_file, '| # | Source Email | Reason');
            \Storage::append($log_file, '| - | - | -');
            $rows = json_decode(\Storage::get($signature_path.'db.json'), true);
            foreach ($rows as $row) {
                if ($user = User::firstWhere('email', $row['fileable_id_email'])) {
                    $row['fileable_id'] = $user->id;
                    $existing_signatures = File::where([
                        'fileable_id'   => $row['fileable_id'],
                        'fileable_type' => $row['fileable_type'],
                    ])->count();
                    if ($existing_signatures) {
                        \Storage::append(
                            $log_file,
                            '| '.number_format(++$errors).' | '.$row['fileable_id_email'].' | Signature already exists'
                        );
                    } else {
                        unset($row['fileable_id_email']);
                        $file = new File();
                        foreach ($row as $field => $value) {
                            $file->$field = $value;
                        }
                        $file->save();
                        $directory = str_replace('files/', '', $file->path);

                        \Debug::debug($signature_path.$directory, $file->path);
                        // Original
                        \Storage::copy($signature_path.$directory, $file->path);
                        // Thumbnail
                        \Storage::copy(
                            str_replace('original.png', 'thumb.png', $signature_path.$directory),
                            str_replace('original.png', 'thumb.png', $file->path)
                        );
                        // Medium
                        \Storage::copy(
                            str_replace('original.png', 'medium.png', $signature_path.$directory),
                            str_replace('original.png', 'medium.png', $file->path)
                        );
                    }
                } else {
                    \Storage::append($log_file, '| '.number_format(++$errors).' | '.$row['fileable_id_email'].' | User not found');
                    $errors++;
                }
            }
            $this->info('Finished importing signatures with '.number_format($errors).' errors');
        }
    }
}
