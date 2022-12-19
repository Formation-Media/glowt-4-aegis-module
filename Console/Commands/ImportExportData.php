<?php

namespace Modules\AEGIS\Console\Commands;

use Illuminate\Console\Command;

class ImportExportData extends Command
{
    protected $base_path = 'modules/aegis/export/';

    public function __construct()
    {
        parent::__construct();
    }
}
