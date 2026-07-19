<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index()
    {
        $users = User::withCount(['projects', 'properties'])->orderBy('name')->paginate(30);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.form', ['user' => new User(['is_active' => true, 'role' => 'viewer']),
            'projects' => Project::orderBy('project_name')->get(), 'assignedCodes' => '']);
    }

    public function store(Request $request)
    {
        $data = $this->validateUser($request);
        $user = DB::transaction(function () use ($data, $request) {
            $user = User::create(['name' => $data['name'], 'email' => $data['email'], 'password' => $data['password'],
                'role' => $data['role'], 'is_active' => $request->boolean('is_active')]);
            $this->syncAccess($user, $request);

            return $user;
        });
        ActivityLog::record('user.created', $user, "Tạo tài khoản {$user->email}");

        return redirect()->route('admin.users.index')->with('success', 'Đã tạo tài khoản.');
    }

    public function edit(User $user)
    {
        $assignedCodes = $user->properties()->orderBy('code')->pluck('code')->implode(', ');

        return view('admin.users.form', compact('user', 'assignedCodes') + ['projects' => Project::orderBy('project_name')->get()]);
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validateUser($request, $user);
        if ($user->is(auth()->user()) && ($data['role'] !== 'admin' || ! $request->boolean('is_active'))) {
            throw ValidationException::withMessages(['role' => 'Không thể hạ quyền hoặc tự khóa tài khoản đang đăng nhập.']);
        }
        if ($user->role === 'admin' && $data['role'] !== 'admin'
            && User::where('role', 'admin')->where('is_active', true)->count() <= 1) {
            throw ValidationException::withMessages(['role' => 'Hệ thống phải còn ít nhất một quản trị viên đang hoạt động.']);
        }
        $payload = ['name' => $data['name'], 'email' => $data['email'], 'role' => $data['role'], 'is_active' => $request->boolean('is_active')];
        if (! empty($data['password'])) {
            $payload['password'] = $data['password'];
        }
        DB::transaction(function () use ($user, $payload, $request) {
            $user->update($payload);
            $this->syncAccess($user, $request);
        });
        ActivityLog::record('user.updated', $user, "Cập nhật quyền {$user->email}");

        return redirect()->route('admin.users.index')->with('success', 'Đã cập nhật tài khoản và phạm vi truy cập.');
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'not_regex:/[\r\n]/', 'max:255', Rule::unique('users')->ignore($user?->id)],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['admin', 'manager', 'viewer'])],
            'project_ids' => ['array'], 'project_ids.*' => ['integer', 'exists:projects,id'],
            'property_codes' => ['nullable', 'string'],
        ]);
    }

    private function syncAccess(User $user, Request $request): void
    {
        $user->projects()->sync($request->input('project_ids', []));
        $codes = collect(preg_split('/[\s,;]+/u', (string) $request->input('property_codes'), -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn ($code) => trim($code))->unique();
        $properties = Property::whereIn('code', $codes)->get(['id', 'code']);
        $missing = $codes->diff($properties->pluck('code'));
        if ($missing->isNotEmpty()) {
            throw ValidationException::withMessages(['property_codes' => 'Không tìm thấy mã căn: '.$missing->implode(', ')]);
        }
        $user->properties()->sync($properties->pluck('id'));
    }
}
