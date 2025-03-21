<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use App\Models\Status;
use App\Models\Service;
use Carbon\Carbon;
use App\Services\ServiceService;

class ServicesController extends Controller
{
    public $name = 'Serviço'; //  singular
    public $folder = 'admin.pages.services';

    protected $serviceService;

    public function __construct(ServiceService $serviceService)
    {
        $this->serviceService = $serviceService;
    }

    public function index()
    {
        return view($this->folder . '.index');
    }

    public function load(Request $request)
    {
        $query = [];
        $filters = $request->only(['name', 'status', 'date_range']);

        if (!empty($filters['name'])) {
            $query['name'] = $filters['name'];
        }

        if (!empty($filters['status'])) {
            $query['status'] = $filters['status'];
        }

        if (!empty($filters['date_range'])) {
            [$startDate, $endDate] = explode(' até ', $filters['date_range']);
            $query['start_date'] = Carbon::createFromFormat('d/m/Y', $startDate)->format('Y-m-d');
            $query['end_date'] = Carbon::createFromFormat('d/m/Y', $endDate)->format('Y-m-d');
        }

        $results = $this->serviceService->getAllServices($filters);

        return view($this->folder . '.index_load', compact('results'));
    }

    public function create()
    {
        $statuses = Status::default();

        return view($this->folder . '.form', compact('statuses'));
    }

    public function store(Request $request)
    {
        $result = $request->all();

        $rules = array(
            'title' => 'required',
            'slug' => 'required',
            'description' => 'nullable',
            'status' => 'required',
            'sort_order' => 'required',
        );
        $messages = array(
            'title.required' => 'title é obrigatório',
            'slug.required' => 'slug é obrigatório',
            'description.required' => 'description é obrigatório',
            'description.nullable' => 'description pode ser nulo',
            'status.required' => 'status é obrigatório',
            'sort_order.required' => 'sort_order é obrigatório',
        );

        $validator = Validator::make($result, $rules, $messages);

        if ($validator->fails()) {
            return response()->json($validator->errors()->first(), 422);
        }

        $service = $this->serviceService->createService($result);

        return response()->json($this->name . ' adicionado com sucesso', 200);
    }

    public function edit($id)
    {
        $result = $this->serviceService->getServiceById($id);
        $statuses = Status::default();

        return view($this->folder . '.form', compact('result', 'statuses'));
    }

    public function update(Request $request, $id)
    {
        $result = $request->all();

        // 'email'         => "unique:services,email,$id,id",
        $rules = array(
            'title' => 'required',
            'slug' => 'required',
            'description' => 'nullable',
            'status' => 'required',
            'sort_order' => 'required',
        );
        $messages = array(
            'title.required' => 'title é obrigatório',
            'slug.required' => 'slug é obrigatório',
            'description.required' => 'description é obrigatório',
            'description.nullable' => 'description pode ser nulo',
            'status.required' => 'status é obrigatório',
            'sort_order.required' => 'sort_order é obrigatório',
        );

        $validator = Validator::make($result, $rules, $messages);

        if ($validator->fails()) {
            return response()->json($validator->errors()->first(), 422);
        }

        $service = $this->serviceService->updateService($id, $result);

        return response()->json($this->name . ' atualizado com sucesso', 200);
    }

    public function delete($id)
    {
        $this->serviceService->deleteService($id);

        return response()->json($this->name . ' excluído com sucesso', 200);
    }
}
