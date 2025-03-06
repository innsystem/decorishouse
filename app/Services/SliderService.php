<?php

namespace App\Services;

use App\Models\Slider;

class SliderService
{
	public function getAllSliders($filters = []) 
	{
		$query = Slider::query();

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

	public function getSliderById($id) 
	{
		return Slider::findOrFail($id);
	}

	public function createSlider($data) 
	{
		return Slider::create($data);
	}

	public function updateSlider($id, $data) 
	{
		$model = Slider::findOrFail($id);
		$model->update($data);
		return $model;
	}

	public function deleteSlider($id) 
	{
		$model = Slider::findOrFail($id);
		return $model->delete();
	}

}
