<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Models\Status;
use App\Models\Permission;
use Carbon\Carbon;
use App\Services\PermissionService;

class PermissionsController extends Controller
{
    public $name = 'Permissões'; //  singular
    public $folder = 'admin.pages.permissions';

    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
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

        $results = $this->permissionService->getAllPermissions($filters);

        return view($this->folder . '.index_load', compact('results'));
    }

    public function create()
    {
        $statuses = Status::default();
        $getRoutes = Route::getRoutes();

        $formattedRoutes = collect($getRoutes)->map(function ($route) {
            return [
                'method' => implode('|', $route->methods()),
                'uri' => str_replace('/', '.', $route->uri()),
                'name' => $route->getName(),
                'action' => $route->getActionName(),
            ];
        });

        $routes = $formattedRoutes->filter(function ($route) {
            return str_starts_with($route['uri'], 'admin');
        });

        return view($this->folder . '.form', compact('statuses', 'routes'));
    }

    public function store(Request $request)
    {
        $result = $request->all();

        $rules = array(
            'title' => 'required|unique:permissions,title',
            'key' => 'required|unique:permissions,key',
        );
        $messages = array(
            'title.required' => 'title é obrigatório',
            'title.unique' => 'title já existe',
            'key.required' => 'key é obrigatório',
            'key.unique' => 'key já existe',
        );

        $validator = Validator::make($result, $rules, $messages);

        if ($validator->fails()) {
            return response()->json($validator->errors()->first(), 422);
        }

        $permission = $this->permissionService->createPermission($result);

        return response()->json($this->name . ' adicionado com sucesso', 200);
    }

    public function edit($id)
    {
        $result = $this->permissionService->getPermissionById($id);
        $statuses = Status::default();
        $getRoutes = Route::getRoutes();

        $formattedRoutes = collect($getRoutes)->map(function ($route) {
            return [
                'method' => implode('|', $route->methods()),
                'uri' => str_replace('/', '.', $route->uri()),
                'name' => $route->getName(),
                'action' => $route->getActionName(),
            ];
        });

        $routes = $formattedRoutes->filter(function ($route) {
            return str_starts_with($route['uri'], 'admin');
        });

        return view($this->folder . '.form', compact('result', 'statuses', 'routes'));
    }

    public function update(Request $request, $id)
    {
        $result = $request->all();

        // 'email'         => "unique:permissions,email,$id,id",
        $rules = array(
            'title' => 'required|unique:permissions,title',
            'key' => 'required|unique:permissions,key',
        );
        $messages = array(
            'title.required' => 'title é obrigatório',
            'title.unique' => 'title já existe',
            'key.required' => 'key é obrigatório',
            'key.unique' => 'key já existe',
        );

        $validator = Validator::make($result, $rules, $messages);

        if ($validator->fails()) {
            return response()->json($validator->errors()->first(), 422);
        }

        $permission = $this->permissionService->updatePermission($id, $result);

        return response()->json($this->name . ' atualizado com sucesso', 200);
    }

    public function delete($id)
    {
        $this->permissionService->deletePermission($id);

        return response()->json($this->name . ' excluído com sucesso', 200);
    }
}
