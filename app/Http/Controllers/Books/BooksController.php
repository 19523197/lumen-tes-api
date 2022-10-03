<?php

namespace App\Http\Controllers\Books;

use App\Models\Books;
use App\Http\Controllers\Controller;
use JD\Cloudder\Facades\Cloudder;
use ImageKit\ImageKit;
use Cloudinary\Configuration\Configuration;

use Carbon\Carbon;
use Illuminate\Http\Response;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;

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
        // $result = (request('sort') == 'desc') ? (Books::orderBy('book_id', 'desc')) : (Books::orderBy('book_id', 'asc'));4
        // dd(request());
        $result = Books::Query();

        $jumlahBuku = Books::count();
        $categorySum = DB::table('books')->select('category', DB::raw('count(*) as total'))
            ->groupBy('category')
            ->get();
        if (request('category') && request('title')) {
            $result = Books::where('category', '=', request('category'))
                ->where('title', 'ilike', '%' . request('title') . '%');

            $jumlahBuku = DB::table('books')
                ->select('category', DB::raw('count(*) as total'))
                ->groupBy('category')->count();
            $categoryIni = DB::table('books')
                ->select('category')->where('category', request('category'))
                ->count();
            $categorySum = DB::table('books')
                ->select('category', DB::raw('count(*) as total'))
                ->where('title', 'ilike', '%' . request('title') . '%')
                ->groupBy('category')
                ->get();
        }
        if (request('title')) {
            $result = $result->where('title', 'ilike', '%' . request('title') . '%');
            (request('sort') == 'desc') ? ($result->orderBy('book_id', 'desc')) : ($result->orderBy('book_id', 'asc'));
            $categoryIni = DB::table('books')
                ->select('category', DB::raw('count(*) as total'))
                ->where('title', 'ilike', '%' . request('title') . '%')
                ->groupBy('category')
                ->get();
            $totalBuku = Books::where('title', 'ilike', '%' . request('title') . '%')->count();
        }

        if (request('category')) {
            $result = Books::where('category', '=', request('category'));
            $categoryIni = DB::table('books')
                ->select('category')->where('category', request('category'))
                ->count();
        }

        if (request('sort') && request('order')) {
            $result = $this->sorting($result, request('sort'), request('order'));
        }

        if (!$result) {
            return response()->json(['message' => 'error'], 404);
        }


        return response()->json(['buku' => $result->paginate(6), 'categorySum' => $categorySum, 'totalBukuStatis' => $jumlahBuku]
            + ((isset($categoryIni)) ? (['totalCategoryIni' => $categoryIni]) : ([])), 200,);
    }

    public function show($id)
    {
        $result = Books::where('book_id', $id)->get();

        if (!$result) {
            return response()->json(['message' => 'error, Buku tidak ditemukan'], 404);
        }

        return response()->json($result, 200);
    }

    public function create(Request $request)
    {

        // $request->createdAt = Carbon::now();
        // $request->updatedAt = Carbon::now();
        if (!$request->header('Authorization')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        // dd($request->cover_url);
        // $image = fopen(__DIR__ . "/" . $request->cover_url->getClientOriginalName(), "r");
        $uploadImage = $this->upload($request);

        $databuku = $request->all();
        $databuku['cover_url'] = $uploadImage->result->url;
        $book = Books::create($databuku);

        if (!$book) {
            return response()->json(['message' => 'error, Buku tidak ditemukan'], 404);
        }

        return response()->json($book, 200);
    }

    public function update($id, Request $request)
    {

        if (!$request->header('Authorization')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $SelectedBook = Books::find($id);
        if (!$SelectedBook) {
            return response()->json(['message' => 'error, Buku tidak ditemukan'], 404);
        }


        if (!is_String($request->cover_url)) {
            $uploadImage = $this->upload($request);
            $databuku = $request->all();
            $databuku['cover_url'] = $uploadImage->result->url;
            $SelectedBook->title = $databuku['title'];
            $SelectedBook->summary = $databuku['summary'];
            $SelectedBook->cover_url = $databuku['cover_url'];
            $SelectedBook->author = $databuku['author'];
            $SelectedBook->category = $databuku['category'];
            $SelectedBook->price = $databuku['price'];
            $SelectedBook->save();
            return response()->json($SelectedBook, 200);
        }

        $SelectedBook->title = $request->title;
        $SelectedBook->summary = $request->summary;
        $SelectedBook->author = $request->author;
        $SelectedBook->category = $request->category;
        $SelectedBook->price = $request->price;
        $SelectedBook->save();
        return response()->json($SelectedBook, 200);
    }

    public function destroy(Request $request, $id)
    {
        if (!$request->header('Authorization')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $result = Books::where('book_id', $id)->delete();

        if (!$result) {
            return response()->json(['message' => 'error'], 404);
        }

        return response()->json(['message' => 'berhasil menghapus buku'], 200);
    }

    //
    public function upload(Request $request)
    {
        // $file_url = "http://yourdomain/defaultimage.png";
        // if ($request->cover_url && $request->cover_url->isValid()) {
        //     $cloudder = Cloudder::upload($request->cover_url->getRealPath());
        //     $uploadResult = $cloudder->getResult();
        //     $file_url = $uploadResult["url"];
        // }
        $imagekit = new ImageKit(
            env('IMAGEKIT_API_KEY'),
            env('IMAGEKIT_API_SECRET'),
            env('IMAGEKIT_URL_ENDPOINT')
        );
        // Upload Image - URL
        $uploadFile = $imagekit->uploadFile([
            "file" => fopen($request->cover_url, "r"),
            "fileName" => $request->cover_url->getClientOriginalName(),
            "useUniqueFileName" => false
        ]);

        return $uploadFile;
    }

    public function sorting($query, $params, $orderBy)
    {
        switch ($params) {
            case 1:
                $paramsKeyQuery = "book_id";
                break;
            case 2:
                $paramsKeyQuery = "title";
                break;
            case 3:
                $paramsKeyQuery = "price";
                break;
        }
        switch ($orderBy) {
            case 1:
                $orderByQuery = "desc";
                break;
            case 2:
                $orderByQuery = "asc";
                break;
        }
        if (!$paramsKeyQuery || !$orderByQuery) {
            return response()->json(['Message' => "error"]);
        }
        return $query->orderBy($paramsKeyQuery, $orderByQuery);
    }
}
