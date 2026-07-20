<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return view('profile.show', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:100', 'alpha_dash', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'birthday' => ['nullable', 'date', 'before_or_equal:today'],
            'gender' => ['nullable', Rule::in(['Nam', 'Nữ', 'Khác'])],
            'phone1' => ['nullable', 'string', 'max:30'],
            'phone2' => ['nullable', 'string', 'max:30'],
            'zalo' => ['nullable', 'string', 'max:255'],
            'skype' => ['nullable', 'string', 'max:255'],
            'facebook' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:3000'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'current_password' => ['nullable', 'required_with:new_password', 'current_password'],
            'new_password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $profile = Arr::only($data, [
            'name', 'username', 'email', 'birthday', 'gender', 'phone1', 'phone2',
            'zalo', 'skype', 'facebook', 'address', 'bio',
        ]);
        if ($request->hasFile('avatar')) {
            $oldAvatar = $user->avatar_path;
            $profile['avatar_path'] = $request->file('avatar')->store('avatars');
            if ($oldAvatar && str_starts_with($oldAvatar, 'avatars/')) {
                Storage::disk('local')->delete($oldAvatar);
            }
        }
        if (! empty($data['new_password'])) {
            $profile['password'] = $data['new_password'];
        }

        $user->update($profile);
        ActivityLog::record('profile.updated', $user, 'Cập nhật hồ sơ cá nhân');

        return back()->with('success', 'Đã cập nhật hồ sơ.');
    }

    public function avatar(Request $request)
    {
        $path = $request->user()->avatar_path;
        abort_unless($path && str_starts_with($path, 'avatars/') && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path), [
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
