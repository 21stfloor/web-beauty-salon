<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $patients = Patient::with('user')->get();

        return response(view('admin.patients.index', compact('patients')));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, User $user, Patient $patient): RedirectResponse
    {
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'contact_number' => 'required|string|regex:/^[0-9]+$/|max:11',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8',
            'birthday' => 'required|date_format:Y-m-d',
            'gender' => 'required|in:male,female,nonbinary,transgender',
            'address' => 'required',
        ]);

        $user->fill([
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'email' => $validatedData['email'],
            'contact_number' => $validatedData['contact_number'],
            'username' => $validatedData['username'],
            'password' => Hash::make($validatedData['password']),
        ])->save();
        $user->assignRole('patient');

        $patient->fill([
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'email' => $validatedData['email'],
            'birthday' => $validatedData['birthday'],
            'gender' => $validatedData['gender'],
            'contact_number' => $validatedData['contact_number'],
            'address' => $validatedData['address'],
            'user_id' => $user->id,
        ])->save();

        return redirect()->route('patients.index')->with('success', "Patient added successfully.");
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Patient $patient): Response
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Patient $patient): RedirectResponse
    {
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($patient->user->id),
            ],
            'contact_number' => 'required|string|max:20',
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users')->ignore($patient->user->id),
            ],
            'birthday' => 'required|date_format:Y-m-d',
            'gender' => 'required|string',
            'address' => 'required',
            'avatar' => 'file',
        ]);

        $userData = [
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'email' => $validatedData['email'],
            'contact_number' => $validatedData['contact_number'],
            'username' => $validatedData['username'],
        ];

        // Check if a new avatar was uploaded
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $avatarPath = $avatar->store('avatars', 'public');
            $userData['avatar'] = $avatarPath;
        }

        $patient->user->update($userData);

        $patientData = [
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'email' => $validatedData['email'],
            'birthday' => $validatedData['birthday'],
            'gender' => $validatedData['gender'],
            'contact_number' => $validatedData['contact_number'],
            'address' => $validatedData['address'],
        ];

        $patient->update($patientData);

        if ($patient->wasChanged() || $patient->user->wasChanged()) {
            return redirect()->route('patients.index')->with('success', 'Patient updated successfully.');
        } else {
            return back()->with('info', 'Nothing has changed.');
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patient $patient): RedirectResponse
    {
        $patient->user()->delete();

        return redirect()->route('patients.index')->with('success', 'Patient and User deleted successfully');
    }
}
