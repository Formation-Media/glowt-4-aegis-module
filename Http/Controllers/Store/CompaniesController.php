<?php

namespace Modules\AEGIS\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Rules\FPDFCompatible;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\AEGIS\Models\Company;

class CompaniesController extends Controller
{

    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required',
            'abbreviation' => [
                'required',
                Rule::unique('m_aegis_companies', 'abbreviation'),
            ],
            'show_for_mdss' => 'nullable',
            'status'        => 'nullable',
            'pdf_footer'    => [
                'nullable',
                'file',
                'max:'.(int) ini_get("upload_max_filesize") * 1024,
                new FPDFCompatible,
            ],
        ]);
        if ($validator->fails()) {
            return redirect($this->link_base.'/add')
                ->withErrors($validator)
                ->withInput();
        }
        $validated              = $validator->validated();
        $company                = new Company();
        $company->abbreviation  = strtoupper($request->abbreviation);
        $company->name          = $validated['name'];
        $company->show_for_mdss = $validated['show_for_mdss'] ?? 0;
        $company->status        = $validated['status'] ?? 0;
        $company->save();
        if ($request->hasFile('pdf_footer')) {
            if (!$file = $company->pdf_footer) {
                $file = new File();
                $file->store($request->pdf_footer, false);
                $file->model($company);
            }
            $file->name = 'PDF Footer';
            $file->save();
        }
        return redirect($this->link_base.'company/'.$company->id);
    }
    public function company(Request $request, $id)
    {
        $redirect  = $this->link_base.'company/'.$id;
        if (!$company = Company::find($id)) {
            return redirect($this->link_base);
        }
        $validator = Validator::make($request->all(), [
            'name'         => 'required',
            'abbreviation' => [
                'required',
                Rule::unique('m_aegis_companies', 'abbreviation')->ignore($id),
            ],
            'show_for_mdss' => 'nullable',
            'status'        => 'nullable',
            'pdf_footer'    => [
                'nullable',
                'file',
                'max:'.(int) ini_get("upload_max_filesize") * 1024,
                new FPDFCompatible,
            ],
        ]);
        if ($validator->fails()) {
            return redirect($redirect)
                ->withErrors($validator)
                ->withInput();
        }
        $validated              = $validator->validated();
        $company->abbreviation  = strtoupper($validated['abbreviation']);
        $company->name          = $validated['name'];
        $company->show_for_mdss = $validated['show_for_mdss'] ?? 0;
        $company->status        = $validated['status'] ?? 0;
        $company->save();
        if ($request->hasFile('pdf_footer')) {
            if (!$file = $company->pdf_footer) {
                $file = new File();
                $file->store($request->pdf_footer, false);
                $file->model($company);
            }
            $file->name = 'PDF Footer';
            $file->save();
        }
        return redirect($redirect);
    }
}
