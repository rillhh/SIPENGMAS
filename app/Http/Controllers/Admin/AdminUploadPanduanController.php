<?php

namespace App\Http\Controllers\Admin;

use App\Models\Panduan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminUploadPanduanController extends Controller
{
    public function store(Request $request)
    {   
        $request->validate([    //validate form
            'file'         => 'required',
            'title'         => 'required|min:1',        
        ]);        
        $file = $request->file('file'); //upload
        $originalName = $file->getClientOriginalName();
        $file->storeAs('panduan', $originalName);
        Panduan::create([
            'file'         => $originalName,
            'title'        => $request->title,
        ]);
        return redirect()->route('admin.dashboard')->with(['success' => 'Data Berhasil Disimpan!']);
    }
}
