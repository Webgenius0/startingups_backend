<?php

namespace App\Http\Controllers\Backend;

use App\Models\User;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BusinessProfile;

class DashboardController extends Controller
{
    public function index() {
        $pending_business = BusinessProfile::where('status', 'pending')->get()->count();
        $accept_business = BusinessProfile::get()->count();
        $subcategories = SubCategory::get()->count();
        $user = User::get()->count();
        $userData = User::paginate(10);
        return view('backend.layouts.index', compact('pending_business', 'accept_business', 'user','userData'));
    }
}
