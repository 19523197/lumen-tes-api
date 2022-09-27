<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JD\Cloudder\Facades\Cloudder;

class CloudinaryController
{
    public function upload(Request $request)
    {
        $file_url = "http://yourdomain/defaultimage.png";
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $cloudder = Cloudder::upload($request->file('image')->getRealPath());
            $uploadResult = $cloudder->getResult();
            $file_url = $uploadResult["url"];
        }
        return $file_url;
    }
}
