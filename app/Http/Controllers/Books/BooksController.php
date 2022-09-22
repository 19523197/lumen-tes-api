<?php

namespace App\Http\Controllers\Books;

use App\Models\Books;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class BooksController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function index()
    {
        $result = Books::all();

        if (!$result) {
            return response()->json(['message' => 'error'], 404);
        }

        return response()->json($result, 200);
    }

    public function show($id)
    {
        $result = Books::where('book_id', $id);

        if (!$result) {
            return response()->json(['message' => 'error'], 404);
        }

        return response()->json($result, 200);
    }

    //
}
