<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Support\ServiceProvider;
use App\Models\Setting;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        view()->composer('*', function ($view) {
            $getSettings = Setting::pluck('value', 'key')->toArray();
            $view->with('getSettings', $getSettings);

            $getCategories = Category::whereNull('parent_id')
                ->with(['children' => function ($query) {
                    $query->orderBy('name', 'ASC'); // Ordenando as subcategorias
                }])
                ->where('status', 1) // Filtrando apenas as categorias ativas
                ->orderBy('name', 'ASC') // Ordenando as categorias principais
                ->get();

            $view->with('getCategories', $getCategories);
        });
    }
}
