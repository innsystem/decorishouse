<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['name', 'status', 'start_date', 'end_date']);
        return response()->json($this->categoryService->getAllCategories($filters));
    }

    public function show($id)
    {
        // Se for um número, busca por ID
        if (is_numeric($id)) {
            return response()->json($this->categoryService->getCategoryById($id));
        }

        // Se for uma string, busca por nome
        return response()->json($this->categoryService->getCategoryByName($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate(array(
            'name' => 'required|string',
            'slug' => 'required|string',
            'thumb' => 'required|string',
            'parent_id' => 'required|string',
            'description' => 'required|string',
            'status' => 'required|string',
        ));
        return response()->json($this->categoryService->createCategory($data), 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate(array(
            'name' => 'required|string',
            'slug' => 'required|string',
            'thumb' => 'required|string',
            'parent_id' => 'required|string',
            'description' => 'required|string',
            'status' => 'required|string',
        ));
        return response()->json($this->categoryService->updateCategory($id, $data));
    }

    public function destroy($id)
    {
        $this->categoryService->deleteCategory($id);
        return response()->json(['message' => 'Category deleted']);
    }
}
