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
        $result = Books::where('book_id', $id)->get();

        if (!$result) {
            return response()->json(['message' => 'error'], 404);
        }

        return response()->json($result, 200);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'title' => 'required',
            'summary' => 'required',
            'cover_url' => 'required',
            'author' => 'required',
            'category' => 'required',
            'price' => 'required|numeric',
        ]);

        $SelectedBook = Books::where('book_id', $id)->get();
        if (!$SelectedBook) {
            return response()->json(['message' => 'error'], 404);
        }

        $SelectedBook->title = $request->title;
        $SelectedBook->summary = $request->summary;
        $SelectedBook->cover_url = $request->cover_url;
        $SelectedBook->author = $request->author;
        $SelectedBook->category = $request->category;
        $SelectedBook->price = $request->price;
        $SelectedBook->save();

        return response()->json($SelectedBook, 200);
    }

    public function destroy($id)
    {
        $result = Books::where('book_id', $id)->delete();

        if (!$result) {
            return response()->json(['message' => 'error'], 404);
        }

        return response()->json(['message' => 'berhasil menghapus buku'], 200);
    }

    //
}
