<?php

namespace Modules\AEGIS\Console\Commands;

use App\Helpers\Dates;
use App\Models\File;
use App\Models\User;
use Modules\Documents\Models\Group;
use Modules\Documents\Models\UserGroup;

class ImportData extends ImportExportData
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aegis:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports user signatures';

    private $data;
    private $log;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->data = json_decode(\Storage::get($this->base_path.'export-data.json'), true);
        $this->log = $this->base_path.'log.md';
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        \Storage::put($this->log, '> Started: '.Dates::datetime());
        \Storage::append($this->log, '');
        $this->import_signatures();
        $this->import_user_groups();
        \Storage::append($this->log, '');
        \Storage::append($this->log, '> Finished: '.Dates::datetime());
    }

    private function import_signatures()
    {
        $directories = \Storage::directories($this->base_path);
        $errors      = 0;

        if ($directories) {
            \Storage::append($this->log, '# Signature Import');
            \Storage::append($this->log, '| # | Source Email | Reason');
            \Storage::append($this->log, '| - | - | -');
            foreach ($this->data['signatures'] as $row) {
                foreach ($row['fileable_id_email'] as $email) {
                    $user = User::withTrashed()->firstWhere('email', $email);
                    if ($user) {
                        break;
                    }
                }
                if ($user) {
                    $row['fileable_id'] = $user->id;
                    $existing_signatures = File::where([
                        'fileable_id'   => $row['fileable_id'],
                        'fileable_type' => $row['fileable_type'],
                    ])->count();
                    if (!$existing_signatures) {
                        unset($row['fileable_id_email']);
                        $file = new File();
                        foreach ($row as $field => $value) {
                            $file->$field = $value;
                        }
                        $file->save();
                        $directory = str_replace('files/', '', $file->path);

                        \Debug::debug($this->base_path.$directory, $file->path);
                        // Original
                        \Storage::copy($this->base_path.$directory, $file->path);
                        // Thumbnail
                        \Storage::copy(
                            str_replace('original.png', 'thumb.png', $this->base_path.$directory),
                            str_replace('original.png', 'thumb.png', $file->path)
                        );
                        // Medium
                        \Storage::copy(
                            str_replace('original.png', 'medium.png', $this->base_path.$directory),
                            str_replace('original.png', 'medium.png', $file->path)
                        );
                    }
                } else {
                    \Storage::append(
                        $this->log,
                        '| '.number_format(++$errors).' | '.implode('<br>', $row['fileable_id_email']).' | User not found'
                    );
                }
            }
            $this->info('Finished importing signatures with '.number_format($errors).' errors');
        }
    }

    private function import_user_groups()
    {
        $errors = 0;

        \Storage::append($this->log, '# User Group Import');
        \Storage::append($this->log, '| # | Source Email | Reason');
        \Storage::append($this->log, '| - | - | -');
        foreach ($this->data['user_groups'] as $group => $users) {
            $group = Group::firstOrCreate([
                'name' => $group,
            ]);
            foreach ($users as $email) {
                foreach ($email as $address) {
                    $user = User::withTrashed()->firstWhere('email', $address);
                    if ($user) {
                        break;
                    }
                }
                if (!$user) {
                    \Storage::append(
                        $this->log,
                        '| '.number_format(++$errors).' | '.implode('<br>', $email).' | User not found'
                    );
                } else {
                    UserGroup::firstOrCreate([
                        'user_id'  => $user->id,
                        'group_id' => $group->id,
                    ]);
                }
            }
        }
    }
}
