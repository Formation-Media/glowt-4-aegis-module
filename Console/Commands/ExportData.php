<?php

namespace Modules\AEGIS\Console\Commands;

use App\Models\File;
use App\Models\User;
use Modules\Documents\Models\Group;

class ExportData extends ImportExportData
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aegis:export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exports user signatures';

    private $data      = [
        'signatures'  => [],
        'user_groups' => [],
    ];

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
        $this->export_user_groups();
        $this->export_signatures();

        \Storage::put($this->base_path.'export-data.json', json_encode($this->data, JSON_PRETTY_PRINT));
        $this->info('Finished exporting data to `/storage/'.$this->base_path.'`');
    }
    private function export_user_groups()
    {
        if ($user_groups = Group::all()) {
            foreach ($user_groups as $user_group) {
                $this->data['user_groups'][$user_group->name] = [];
                foreach ($user_group->users as $user) {
                    $this->data['user_groups'][$user_group->name][] = [
                        $user->email,
                        $user->first_name.$user->last_name.'@aegisengineering.co.uk',
                        substr($user->first_name, 0, 1).'.'.$user->last_name.'@aegisengineering.co.uk',
                        substr($user->first_name, 0, 1).'.'.$user->last_name.'@aegiseng.co.uk',
                        substr($user->first_name, 0, 1).'.'.$user->last_name.'@aegis-cert.co.uk',
                    ];
                }
            }
        }
    }
    private function export_signatures()
    {
        $signatures = File::where('fileable_type', User::class);
        if ($signatures->count() > 0) {
            foreach ($signatures->get() as $signature) {
                if ($signature->fileable) {
                    $db_entry = $signature->toArray();
                    $db_entry['fileable_id']       = null;
                    $db_entry['fileable_id_email'] = [
                        $signature->fileable->email,
                        $signature->fileable->first_name.$signature->fileable->last_name.'@aegisengineering.co.uk',
                        substr($signature->fileable->first_name, 0, 1).'.'.$signature->fileable->last_name.'@aegisengineering.co.uk',
                        substr($signature->fileable->first_name, 0, 1).'.'.$signature->fileable->last_name.'@aegiseng.co.uk',
                        substr($signature->fileable->first_name, 0, 1).'.'.$signature->fileable->last_name.'@aegis-cert.co.uk',
                    ];
                    $db_entry['path'] = $signature->getRawOriginal('path');
                    unset(
                        $db_entry['fileable'],
                        $db_entry['id']
                    );

                    $this->data['signatures'][] = $db_entry;
                    $path                       = $this->base_path.str_replace('files/', '', $signature->path);
                    $directory                  = dirname($path);
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
    }
}
