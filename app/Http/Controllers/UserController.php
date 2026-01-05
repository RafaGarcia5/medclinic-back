<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\DoctorResource;

class UserController extends Controller
{
    public function doctors(Request $request)
    {
        $query = User::where('role', 'doctor')->with('services:id,name');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%');
            });
        }
        
        $perPage = $request->query('per_page', 10);
        $doctors = $query->paginate($perPage);
        return DoctorResource::collection($doctors);
    }

    public function patients(Request $request)
    {
        $query = User::where('role', 'patient');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%');
            });
        }
        
        $perPage = $request->query('per_page', 10);
        return $query->paginate($perPage);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->role === 'admin') {
            return response()->json(['error' => 'An administrator cannot be removed'], 403);
        }

        $user->delete();
        return response()->json(['message' => 'User successfully deleted']);
    }

}
