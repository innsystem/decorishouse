<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;
use App\Services\UserService;

class CustomersController extends Controller
{
    public $name = 'Cliente'; //  singular
    public $folder = 'admin.pages.customers';

    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        return view($this->folder . '.index');
    }

    public function load(Request $request)
    {
        $query = [];
        $filters = $request->only(['name', 'status', 'date_range']);

        $filters['user_group_id'] = 3; // User Group Id for Customers

        if (!empty($filters['name'])) {
            $filters['name'] = $filters['name'];
        }

        if (!empty($filters['status'])) {
            $filters['status'] = $filters['status'];
        }

        if (!empty($filters['date_range'])) {
            [$startDate, $endDate] = explode(' até ', $filters['date_range']);
            $filters['start_date'] = Carbon::createFromFormat('d/m/Y', $startDate)->format('Y-m-d');
            $filters['end_date'] = Carbon::createFromFormat('d/m/Y', $endDate)->format('Y-m-d');
        }

        $results = $this->userService->getAllUsers($filters);

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
            'name' => 'required',
            'email' => 'required|unique:users,email',
            'password' => 'required',
            'document' => 'nullable',
            'phone' => 'nullable',
        );
        $messages = array(
            'name.required' => 'name é obrigatório',
            'email.required' => 'email é obrigatório',
            'email.unique' => 'email já existe',
            'password.required' => 'password é obrigatório',
            'password.nullable' => 'password pode ser nulo',
            'document.required' => 'document é obrigatório',
            'document.nullable' => 'document pode ser nulo',
            'phone.required' => 'phone é obrigatório',
            'phone.nullable' => 'phone pode ser nulo',
        );

        $validator = Validator::make($result, $rules, $messages);

        if ($validator->fails()) {
            return response()->json($validator->errors()->first(), 422);
        }

        $result['user_group_id'] = 3; // User Group Id for Customers

        $user = $this->userService->createUser($result);

        return response()->json($this->name . ' adicionado com sucesso', 200);
    }

    public function show($id)
    {
        $customer = $this->userService->getUserById($id);
        $customerInvoices = $customer->invoices;

        return view($this->folder . '.show', compact('customer', 'customerInvoices'));
    }

    public function edit($id)
    {
        $result = $this->userService->getUserById($id);
        $statuses = Status::default();

        return view($this->folder . '.form', compact('result', 'statuses'));
    }

    public function update(Request $request, $id)
    {
        $result = $request->all();

        $rules = array(
            'name' => 'required',
            'email' => "required|unique:users,email,$id,id",
            'password' => 'nullable',
            'document' => 'nullable',
            'phone' => 'nullable',
        );
        $messages = array(
            'name.required' => 'name é obrigatório',
            'email.required' => 'email é obrigatório',
            'email.unique' => 'email já existe',
            'password.required' => 'password é obrigatório',
            'password.nullable' => 'password pode ser nulo',
            'document.required' => 'document é obrigatório',
            'document.nullable' => 'document pode ser nulo',
            'phone.required' => 'phone é obrigatório',
            'phone.nullable' => 'phone pode ser nulo',
        );

        $validator = Validator::make($result, $rules, $messages);

        if ($validator->fails()) {
            return response()->json($validator->errors()->first(), 422);
        }

        $user = $this->userService->updateUser($id, $result);

        return response()->json($this->name . ' atualizado com sucesso', 200);
    }

    public function delete($id)
    {
        $this->userService->deleteUser($id);

        return response()->json($this->name . ' excluído com sucesso', 200);
    }
}
