<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if($user->role !== 'admin') {
            return Service::all();
        }
        
        $query = Service::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                ->orWhere('description', 'like', '%' . $search . '%');
            });
        }
        
        $perPage = $request->query('per_page', 10);
        return $query->paginate($perPage);
    }

    public function doctors($id)
    {
        $service = Service::with('doctors')->findOrFail($id);
        return $service->doctors;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string',
            'description'   => 'required|string'
        ]);

        return Service::create($validated);
    }

    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        $validated = $request->validate([
            'name'        => 'sometimes|string',
            'description' => 'sometimes|string',
        ]);

        $service->update($validated);

        return response()->json([
            'message' => 'Service updated successfully',
            'service' => $service
        ], 200);
    }

    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        $service->delete();

        return response()->json(['message' => 'Service successfully removed'], 200);
    }
}
