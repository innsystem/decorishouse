<?php

namespace App\Services;

use App\Models\Testimonial;

class TestimonialService
{
	public function getAllTestimonials($filters = []) 
	{
		$query = Testimonial::query();

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

	public function getTestimonialById($id) 
	{
		return Testimonial::findOrFail($id);
	}

	public function createTestimonial($data) 
	{
		return Testimonial::create($data);
	}

	public function updateTestimonial($id, $data) 
	{
		$model = Testimonial::findOrFail($id);
		$model->update($data);
		return $model;
	}

	public function deleteTestimonial($id) 
	{
		$model = Testimonial::findOrFail($id);
		return $model->delete();
	}

}
