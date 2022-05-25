<?php

namespace App\Http\Controllers\API;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::paginate(10);
        $data['posts'] = $posts;

        return response()->json([
            'response_code' => '00',
            'response_message' => 'data posts berhasil ditampilkan',
            'data' => $data,
            'hasMorePages' => $posts->hasMorePages()
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'body' => 'required',
            'photo' => 'required|mimes:jpg, jpeg, png'
        ]);

        $photo = $request->file('photo');
        $photo->storeAs('public/photos', $photo->hashName());

        $post = Post::create([
            'title' => $request->title,
            'body' => $request->body,
            'photo'     => $photo->hashName(),
        ]);

        return response()->json([
            'response_code' => '00',
            'response_message' => 'Berhasil Menambahkan Post',
            'data' => $post
        ], 200);    
    }

    public function update(Request $request, $id)
    {
        
        $request->validate([
            'title' => 'required',
            'body' => 'required',
        ]);
        
        if ($request->file('photo') == "") {
            $post = Post::findOrFail($id);
            $post->update([
                'title'  => $request->input('title'),
                'body'   => $request->input('body')  
            ]);

            return response()->json([
                'response_code' => '00',
                'response_message' => 'data post berhasil diupdate',
                'data' => $post,
            ], 200); 
            
        } else {
            $post = Post::findOrFail($id);
            Storage::disk('local')->delete('public/photos/'.$post->photo);

            $photo = $request->file('photo');
            $photo->storeAs('public/photos', $photo->hashName());

            $post->update([
                'photo'       => $photo->hashName(),
                'title'       => $request->input('title'),
                'body'     => $request->input('body')  
            ]);

            return response()->json([
                'response_code' => '00',
                'response_message' => 'data post berhasil diupdate',
                'data' => $post,
            ], 200);     
        }
    }

    public function delete($id)
    {
        $post = Post::findOrFail($id);
        Storage::disk('local')->delete('public/photos/'.basename($post->photo));
        $post->delete();

        return response()->json([
            'response_code' => '00',
            'response_message' => 'data post berhasil dihapus',
            'data' => $post,
        ], 200);  
    }
}
