<?php

namespace Modules\AEGIS\Models;

use \App\Models\Base_Model;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use Base_Model;
    use SoftDeletes;

    protected $fillable = [
        'added_by',
        'name',
        'reference',
    ];
    protected $table = 'm_aegis_scopes';

    public function projects()
    {
        return $this->hasMany(Project::class, 'scope_id', 'id');
    }
    public function save(array $options = [])
    {
        $user_id = \Auth::id() ?? 1;
        if (!$this->added_by) {
            $this->added_by = $user_id;
        }
        if (!$this->reference) {
            $i                = 0;
            $reference        = strtoupper(substr($this->name, 0, 3));
            $reference_suffix = str_pad(++$i, max(0, 6 - strlen($reference)), '0', STR_PAD_LEFT);
            $customer_count = Customer::where('reference', $reference.$reference_suffix)->count();
            while ($customer_count > 0) {
                // When the loop stops we've got the reference #
                $reference_suffix = str_pad(++$i, max(0, 6 - strlen($reference)), '0', STR_PAD_LEFT);

                $customer_count   = Customer::where('reference', $reference.$reference_suffix)->count();
                continue;
            }
            $this->reference = $reference.$reference_suffix;
        }
        parent::save($options);
    }
}
