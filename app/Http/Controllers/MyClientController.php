<?php

namespace App\Http\Controllers;

use App\Models\MyClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class MyClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clients = MyClient::all();
        return response()->json($clients);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:250',
            'slug' => 'required|string|max:100|unique:my_client,slug',
            'is_project' => 'in:0,1',
            'self_capture' => 'string|max:1',
            'client_prefix' => 'required|string|max:4',
            'client_logo' => 'nullable|image|mimes:jpg,jpeg,png',
            'address' => 'nullable|string',
            'phone_number' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:50',
        ]);

        if ($request->hasFile('client_logo')) {
            $path = $request->file('client_logo')->store('clients', 's3');
            $data['client_logo'] = Storage::disk('s3')->url($path);
        }

        $client = MyClient::create($data);
        Redis::set("client:{$client->slug}", json_encode($client));

        return response()->json($client, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($slug)
    {
        $client = Redis::get("client:$slug");

        if (!$client) {
            $client = MyClient::where('slug', $slug)->firstOrFail();
            Redis::set("client:$slug", json_encode($client));
        } else {
            $client = json_decode($client);
        }

        return response()->json($client);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
