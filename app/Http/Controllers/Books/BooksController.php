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

        $image = $request->cover_url;

        // Upload Image - URL
        $uploadFile = $imagekit->uploadFile([
            "file" => fopen($request->cover_url, "r"),
            "fileName" => $request->cover_url->getClientOriginalName(),
            "useUniqueFileName" => true
        ]);



        return $uploadFile;
    }

    public function index()
    {

        $result = (request('sort') == 'desc') ? (Books::orderBy('book_id', 'desc')) : (Books::orderBy('book_id', 'asc'));

        $jumlahBuku = Books::count();
        $categorySum = DB::table('books')->select('category', DB::raw('count(*) as total'))
            ->groupBy('category')
            ->get();
        if (request('category') == 0 && request('title') == NULL) {
            return response()->json(['buku' => $result->paginate(6), 'categorySum' => $categorySum, 'totalBukuStatis' => $jumlahBuku], 200,);
        }
        if (request('category') && request('title')) {
            $category = Books::where('category', '=', request('category'))
                ->where('title', 'ilike', '%' . request('title') . '%');
            (request('sort') == 'desc') ? ($category->orderBy('book_id', 'desc')) : ($category->orderBy('book_id', 'asc'));

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
            return response()->json(['buku' => $category->paginate(6), 'totalBukuStatis' => $jumlahBuku, 'totalCategoryIni' => $categoryIni, 'totalCategory' => $categorySum,], 200);
        }
        if (request('title')) {
            $result = Books::where('title', 'ilike', '%' . request('title') . '%');
            (request('sort') == 'desc') ? ($result->orderBy('book_id', 'desc')) : ($result->orderBy('book_id', 'asc'));
            $categoryIni = DB::table('books')
                ->select('category', DB::raw('count(*) as total'))
                ->where('title', 'ilike', '%' . request('title') . '%')
                ->groupBy('category')
                ->get();
            $totalBuku = Books::where('title', 'ilike', '%' . request('title') . '%')->count();
            return response()->json(['buku' => $result->paginate(6), 'totalCategoryIni' => $categoryIni, 'totalBukuStatis' => $totalBuku], 200);
        }
        if (request('category')) {
            $category = Books::where('category', '=', request('category'));
            (request('sort') == 'desc') ? ($category->orderBy('book_id', 'desc')) : ($category->orderBy('book_id', 'asc'));
            $categoryIni = DB::table('books')
                ->select('category')->where('category', request('category'))
                ->count();
            return response()->json(['buku' => $category->paginate(6), 'totalCategoryIni' => $categoryIni, 'totalCategory' => $categorySum, 'totalBukuStatis' => $jumlahBuku], 200);
        }

        if (!$result) {
            return response()->json(['message' => 'error'], 404);
        }

        return response()->json(['buku' => $result->paginate(6), 'categorySum' => $categorySum, 'totalBukuStatis' => $jumlahBuku], 200,);
    }

    public function show($id)
    {
        $result = Books::where('book_id', $id)->get();

        if (!$result) {
            return response()->json(['message' => 'error'], 404);
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
            return response()->json(['message' => 'error'], 404);
        }

        return response()->json($book, 200);
    }

    public function update(Request $request, $id)
    {
        if (!$request->header('Authorization')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $this->validate($request, [
            'title' => 'required',
            'summary' => 'required',
            'cover_url' => 'required',
            'author' => 'required',
            'category' => 'required',
            'price' => 'required|numeric',
        ]);

        $SelectedBook = Books::find($id);
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


}
