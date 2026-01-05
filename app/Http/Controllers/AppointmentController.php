<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if($user->role === 'admin') {
            $search = $request->input('search');
            $date = $request->input('date');
            $perPage = $request->input('per_page', 10);

            $query = Appointment::with(['patient', 'doctor', 'service'])
                ->when($search, function ($q) use ($search) {
                    $q->where(function ($query) use ($search) {
                        $query->whereHas('patient', function ($q) use ($search) {
                            $q->where('name', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('doctor', function ($q) use ($search) {
                            $q->where('name', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('service', function ($q) use ($search) {
                            $q->where('name', 'like', '%' . $search . '%');
                        })
                        ->orWhere('date', 'like', '%' . $search . '%')
                        ->orWhere('time', 'like', '%' . $search . '%')
                        ->orWhere('status', 'like', '%' . $search . '%');
                    });
                })
                ->when($date, function ($q) use ($date) {
                    $q->whereDate('date', $date);
                })
                ->orderBy('date', 'desc');

            return $query->paginate($perPage);
        }

        if($user->role === 'doctor') 
            return Appointment::with('patient','service')
                ->where('doctor_id',$user->id)
                ->orderBy('date', 'desc')
                ->get();

        if($user->role === 'patient') 
            return Appointment::with('doctor','service')
                ->where('patient_id',$user->id)
                ->orderBy('date','desc')
                ->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'doctor_id'=>'required|exists:users,id',
            'service_id'=>'required|exists:services,id',
            'date'=>'required|date',
            'time'=>'required',
            'medical_record'=>'nullable|string'
        ]);

        $exists = Appointment::where('doctor_id',$request->doctor_id)
            ->where('date',$request->date)
            ->where('time',$request->time)
            ->whereIn('status',['active','rescheduled'])
            ->exists();

        if($exists) return response()->json(['error'=>'El doctor ya tiene una cita en ese horario'],422);

        $appointment = Appointment::create([
            'patient_id'=>$request->user()->id,
            'doctor_id'=>$request->doctor_id,
            'service_id'=>$request->service_id,
            'date'=>$request->date,
            'time'=>$request->time,
            'medical_record'=>$request->medical_record,
            'status'=>Appointment::STATUS_ACTIVE
        ]);

        return response()->json($appointment,201);
    }

    public function update(Request $request,$id)
    {
        $appointment = Appointment::findOrFail($id);
        $validated = $request->validate([
            'doctor_id'=>'sometimes|exists:users,id',
            'service_id'=>'sometimes|exists:services,id',
            'date'=>'sometimes|date',
            'time'=>'sometimes',
            'medical_record'=>'sometimes',
            'status' => 'sometimes|string'
        ]);

        $appointment->update($validated);
        return response()->json($appointment);
    }

    public function destroy($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update(['status' => Appointment::STATUS_CANCELLED]);
        return response()->json(['message' => 'Appointment cancelled']);
    }

    public function reschedule(Request $request,$id)
    {
        $appointment = Appointment::findOrFail($id);

        $request->validate(['date'=>'required|date','time'=>'required']);

        $exists = Appointment::where('doctor_id',$appointment->doctor_id)
            ->where('date',$request->date)
            ->where('time',$request->time)
            ->whereIn('status',['active','rescheduled'])
            ->exists();

        if($exists) return response()->json(['error'=>'El doctor ya tiene una cita en ese horario'],422);

        $appointment->update([
            'date'=>$request->date,
            'time'=>$request->time,
            'status'=>Appointment::STATUS_RESCHEDULED
        ]);

        return response()->json($appointment);
    }
}
