<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TinChiController extends Controller
{
    function index() {
        return view('tinchi.index');
    }

    function upload(Request $request) {
        if($request->input('password') !== env('TINCHI_PASSWORD'))
            return back()->withInput()->with('error', 'Password hem đúng');

        if(!$request->hasFile('json') || !$request->hasFile('excel'))
            return back()->withInput()->with('error', 'File hem có');
        
        $json = $request->file('json');
        if(!(mime_content_type($json->path()) !== 'text/plain'))
            return back()->withInput()->with('error', 'File json hem đúng');

        $excel = $request->file('excel');
        if(!str_contains(mime_content_type($excel->path()), 'sheet'))
            return back()->withInput()->with('error', 'File Excel hem đúng');

        $jsonData = json_decode(file_get_contents($json->getRealPath()), true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_string($jsonData['title'] ?? null) || !is_array($jsonData['data'] ?? null))
            return back()->withInput()->with('error', 'File json hem đúng');

        $json->storeAs('public', 'tinchi.json');
        $excel->storeAs('public', 'tinchi.xlsx');

        if (json_last_error() !== JSON_ERROR_NONE)
            return back()->withInput()->with('error', 'Có lỗi xảy ra');

        return back()->withInput()->with('success', 'Upload thành công');
    }
}
