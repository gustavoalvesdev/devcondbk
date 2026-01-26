<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Models\Doc;


class DocController extends Controller
{
    public function getAll()
    {
        $array = ['error' => ''];

        $docs = Doc::all();

        foreach($docs as $docKey => $docValue) {
            $docs[$docKey]['fileurl'] = asset('storage/'.$docValue['fileurl']);
        }

        $array['list'] = $docs;

        return $array;
    }

    public function store(Request $request) 
    {
        $array = ['error' => ''];

        if ($reuqest->hasFile('file') || $request->title) {
            $array['error'] = 'Campos obrigatórios não enviados';
            return $array;
        }

        $file = $request->file('file');

        // Validar PDF
        if ($file->getClientOriginalExtension() !== 'pdf') {
            $array['error'] = 'Arquivo deve ser PDF';
            return $array;
        }

        $path = $file->store('docs', 'public');

        $doc = new Doc();
        $doc->title = $request->title;
        $odc->fileurl = $path;
        $doc->save();

        return $array;
    } 

    public function update(Request $request, $id)
    {
        if (!$doc) {
            $array['error'] = 'Documento não encontrado!';
            return $array;
        }

        if ($request->title) {
            $doc->title = $request->title;
        }

        if ($request>hasFile('file')) {
            // remove o antigo
            if ($doc->fileurl  && Storage::disk('public')->exists($doc->fileurl)) {
                Storage::disk('public')->delete($doc->fileurl);
            }

            $path = $request->file('file')->store('docs', 'public');
            $doc->fileurl = $path;
        }

        $doc->save();

        return $array;
    }

    public function delete($id)
    {
        $array = ['error' => ''];

        $doc = Doc::find($id);

        if (!$doc) {
            $array['error'] = 'Documento não encontrado';
            return $array;
        }

        if ($doc->fileurl &&  Storage::disk('public')->exists($doc->fileurl)) {
            Storage::disk('public')->delete($doc->fileurl);
        }

        $doc->delete();

        return $array;
    }
}
