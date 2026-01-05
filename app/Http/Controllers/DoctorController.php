<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;

class DoctorController extends Controller
{
    public function availability(Request $request, $doctorId)
    {
        $date = $request->query('date');

        if (!$date) {
            return response()->json(['error' => 'Date is required'], 400);
        }

        $occupiedTimes = Appointment::where('doctor_id', $doctorId)
            ->where('date', $date)
            ->whereIn('status', ['active', 'rescheduled'])
            ->pluck('time')
            ->map(function ($time) {
                return Carbon::parse($time)->format('H:i');
            })
            ->toArray();

        $availableTimes = [];
        $start = Carbon::createFromTime(8, 0);
        $end = Carbon::createFromTime(17, 0);

        while ($start < $end) {
            $slot = $start->format('H:i');
            if (!in_array($slot, $occupiedTimes)) {
                $availableTimes[] = $slot;
            }
            $start->addHour();
        }

        return response()->json([
            'available' => $availableTimes,
            'unavailable' => $occupiedTimes
        ]);
    }

    public function myServices(Request $request)
    {
        return response()->json($request->user()->services);
    }

    public function unlinkedServices(Request $request)
    {
        $doctor = $request->user();
        $linkedServiceIds = $doctor->services()->pluck('services.id');
        $unlinkedServices = Service::whereNotIn('id', $linkedServiceIds)->get();

        return response()->json($unlinkedServices);
    }

    public function attachService(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
        ]);

        $user = $request->user();
        $user->services()->syncWithoutDetaching([$request->service_id]);

        return response()->json($user->services);
    }

    public function unlinkService(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
        ]);

        $user = $request->user();
        $user->services()->detach($request->service_id);

        return response()->json($user->services);
    }
}
