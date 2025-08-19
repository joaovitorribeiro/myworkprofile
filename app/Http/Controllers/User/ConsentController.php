<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ConsentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // You can add your logic here
        return view('consent.index');
    }

    // Add other methods (create, store, show, edit, update, destroy) as needed
}