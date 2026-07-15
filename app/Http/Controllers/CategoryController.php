<?php

namespace App\Http\Controllers;

use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * List all article categories.
     */
    public function index()
    {
        return Category::orderBy('name')->get();
    }
}
