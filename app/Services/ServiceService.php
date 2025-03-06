<?php

namespace App\Services;

use App\Models\Service;

class ServiceService
{
	public function getAllServices($filters = []) 
	{
		$query = Service::query();

		if (!empty($filters['name'])) {
			$query->where('title', 'LIKE', '%' . $filters['name'] . '%');
		}

		if (!empty($filters['status'])) {
			$query->where('status', $filters['status']);
		}

		if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
			$query->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);
		}

		return $query->get(); 
	}

	public function getServiceById($id) 
	{
		return Service::findOrFail($id);
	}

	public function createService($data) 
	{
		return Service::create($data);
	}

	public function updateService($id, $data) 
	{
		$model = Service::findOrFail($id);
		$model->update($data);
		return $model;
	}

	public function deleteService($id) 
	{
		$model = Service::findOrFail($id);
		return $model->delete();
	}

}
