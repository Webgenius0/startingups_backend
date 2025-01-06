<?php

namespace App\Http\Controllers\Backend;

use App\Models\User;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index() {
        $categories = Category::get()->count();
        $subcategories = SubCategory::get()->count();
        $user = User::get()->count();
        $userData = User::get();
        return view('backend.layouts.index', compact('categories', 'subcategories', 'user','userData'));
    }
}
