<?php

namespace Modules\AEGIS\Imports;

use App\Helpers\SSEStream;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Modules\AEGIS\Models\JobTitle;
use Modules\AEGIS\Models\UserGrade;

class UsersImport implements ToCollection
{
    private $stream;

    public function __construct(SSEStream $stream)
    {
        $this->stream = $stream;
    }
    public function collection(Collection $rows)
    {
        $this->stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;Loading previous data',
        ]);
        $grades = UserGrade::ordered()->pluck('name', 'id')->map(function ($value) {
            return strtolower($value);
        });
        $job_titles = JobTitle::ordered()->pluck('name', 'id')->map(function ($value) {
            return strtolower($value);
        });
        $this->stream->send([
            'percentage' => 0,
            'message'    => '&nbsp;&nbsp;&nbsp;Processing import file',
        ]);
        foreach ($rows as $i => $row) {
            if ($i === 0 || !isset($row[1])) {
                continue;
            }

            $this->row = $row;

            $is_external = strtolower($this->column('is_external')) === 'yes' ? true : false;
            $job_title   = strtolower($this->column('job_title'));
            $reference   = $this->column('reference');
            $roles       = [];
            $status      = strtolower($this->column('is_active')) === 'yes' ? true : false;

            # If email exists
            if (!($user = User::withTrashed()->firstWhere('email', $this->column('email')))) {
                $user = User::create([
                    'title'      => '',
                    'first_name' => $this->column('first_name'),
                    'last_name'  => $this->column('last_name'),
                    'email'      => $this->column('email'),
                    'status'     => $status,
                ]);
            }

            $disciplines   = $user->getMeta('aegis.discipline');
            $disciplines[] = array_search($job_title, $job_titles->toArray());

            $user->setMeta([
                'aegis.discipline'       => array_unique($disciplines),
                'aegis.grade'            => array_search($job_title, $grades->toArray()),
                'aegis.import-reference' => $reference,
                'aegis.type'             => $is_external ? 1 : 2,
                'aegis.user-reference'   => $this->column('abbreviation'),
            ]);
            $user->save();

            if ($is_external) {
                $roles[] = config('roles.by_name.core.staff');
            } else {
                $roles[] = config('roles.by_name.core.user');
            }
            $user->roles()->sync($roles);
            $this->stream->send([
                'percentage' => round(($i + 1) / count($rows) * 100, 1),
            ]);
        }
    }
    private function column($key)
    {
        $columns = [
            'reference'          => 'USER-NICKNAME',
            'job_title'          => 'USER-ROLE',
            'first_name'         => 'USER-NAME',
            'last_name'          => 'USER-SURNAME',
            'role'               => 'USER-PROFILE',
            'email'              => 'E-MAIL',
            'abbreviation'       => 'SHORT-NAME',
            'signature'          => 'SIGNATURE',
            'is_assessor'        => 'Assessor',
            'is_project_manager' => 'Proj_Manager',
            'is_active'          => 'Active',
            'is_external'        => 'External',
            'is_lead_assessor'   => 'Lead_Assessor',
        ];

        return trim(preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $this->row[array_search($columns[$key], array_values($columns))]));
    }
}
