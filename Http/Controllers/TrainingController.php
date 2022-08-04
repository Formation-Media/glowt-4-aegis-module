<?php

namespace Modules\AEGIS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\AEGIS\Helpers\Icons;
use Modules\AEGIS\Helpers\Training as TrainingHelper;
use Modules\AEGIS\Models\Training;

class TrainingController extends Controller
{
    public function index()
    {
        $page_menu = [
            [
                'href'  => $this->link_base.'add',
                'icon'  => Icons::training(),
                'title' => ['phrases.add', ['item' => 'aegis::dictionary.training']],
            ],
        ];

        return parent::view(compact(
            'page_menu'
        ));
    }

    public function training(Request $request, $id)
    {
        $training       = Training::findOrFail($id);
        $training_names = TrainingHelper::periods();
        $users          = User::staff()->ordered('first_name')->active()->get()->pluck('name', 'id');

        return parent::view(compact(
            'training',
            'training_names',
            'users',
        ));
    }

    public function add(Request $request, $id = null)
    {
        $training_names = TrainingHelper::periods();
        $users          = User::staff()->ordered('first_name')->active()->get()->pluck('name', 'id');
        return parent::view(compact(
            'training_names',
            'users',
        ));
    }
}
