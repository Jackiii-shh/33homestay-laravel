<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index()
    {
        return view('owner.users.index', ['users' => User::orderBy('role')->orderBy('name')->get()]);
    }

    public function create()
    {
        return view('owner.users.form', ['user' => new User(['role' => User::ROLE_PENGELOLA, 'is_active' => true])]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:25'],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create($data + ['is_active' => $request->boolean('is_active', true)]);
        ActivityLog::record('user_changed', $user, 'Pengguna '.$user->name.' ('.$user->roleLabel().') dibuat.');

        return redirect()->route('owner.users.index')->with('success', 'Pengguna ditambahkan.');
    }

    public function edit(User $user)
    {
        return view('owner.users.form', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:25'],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $active = $request->boolean('is_active');

        if ($user->id === $request->user()->id && (! $active || $data['role'] !== User::ROLE_OWNER)) {
            throw ValidationException::withMessages(['role' => 'Anda tidak dapat menonaktifkan atau menurunkan peran akun Anda sendiri.']);
        }

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data + ['is_active' => $active]);
        ActivityLog::record('user_changed', $user, 'Pengguna '.$user->name.' diperbarui ('.$user->roleLabel().', '.($active ? 'aktif' : 'nonaktif').').');

        return redirect()->route('owner.users.index')->with('success', 'Pengguna diperbarui.');
    }
}
