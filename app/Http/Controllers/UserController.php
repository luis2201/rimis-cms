<?php

namespace App\Http\Controllers;

use App\Models\ResearcherProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    private const ASSIGNABLE_ROLES = ['WEBMASTER', 'INVESTIGADOR'];

    public function index(Request $request): View
    {
        $roles = ['ADMINISTRADOR', ...self::ASSIGNABLE_ROLES];
        $roleFilter = $request->string('role')->toString();
        $statusFilter = $request->string('status')->toString();

        $users = User::query()
            ->with('roles')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim();

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(in_array($roleFilter, $roles, true), function ($query) use ($roleFilter) {
                $query->role($roleFilter);
            })
            ->when(in_array($statusFilter, ['active', 'inactive'], true), function ($query) use ($statusFilter) {
                $query->where('is_active', $statusFilter === 'active');
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', array_merge([
            'roles' => self::ASSIGNABLE_ROLES,
        ], $this->researcherFormOptions()));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(array_merge([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::in(self::ASSIGNABLE_ROLES)],
        ], $this->researcherRules($request)));

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);
        $this->saveResearcherProfile($request, $user, $validated);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user): View
    {
        $user->load('roles');

        return view('admin.users.edit', array_merge([
            'user' => $user,
            'roles' => self::ASSIGNABLE_ROLES,
        ], $this->researcherFormOptions()));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ];

        if (! $user->hasRole('ADMINISTRADOR')) {
            $rules['role'] = ['required', Rule::in(self::ASSIGNABLE_ROLES)];
            $rules = array_merge($rules, $this->researcherRules($request, $user));
        }

        $validated = $request->validate($rules);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        if (! $user->hasRole('ADMINISTRADOR')) {
            $user->syncRoles([$validated['role']]);
            $this->saveResearcherProfile($request, $user, $validated);
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function deactivate(Request $request, User $user): RedirectResponse
    {
        abort_if($request->user()->is($user) || $user->hasRole('ADMINISTRADOR'), 403);

        $user->deactivate();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Usuario desactivado correctamente.');
    }

    public function activate(User $user): RedirectResponse
    {
        abort_if($user->hasRole('ADMINISTRADOR'), 403);

        $user->activate();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Usuario activado correctamente.');
    }

    private function researcherFormOptions(): array
    {
        return [
            'salutations' => ResearcherProfile::SALUTATIONS,
            'countries' => ResearcherProfile::COUNTRIES,
            'researchAreas' => ResearcherProfile::RESEARCH_AREAS,
        ];
    }

    private function researcherRules(Request $request, ?User $user = null): array
    {
        $required = Rule::requiredIf($request->input('role') === 'INVESTIGADOR');
        $cvRequired = Rule::requiredIf(
            $request->input('role') === 'INVESTIGADOR' && ! $user?->researcherProfile?->cv_path
        );

        return [
            'country' => [$required, 'nullable', Rule::in(ResearcherProfile::COUNTRIES)],
            'salutation' => [$required, 'nullable', Rule::in(ResearcherProfile::SALUTATIONS)],
            'academic_title' => [$required, 'nullable', 'string', 'max:150'],
            'profession' => [$required, 'nullable', 'string', 'max:150'],
            'research_area' => [$required, 'nullable', Rule::in(ResearcherProfile::RESEARCH_AREAS)],
            'institution' => [$required, 'nullable', 'string', 'max:255'],
            'phone' => [$required, 'nullable', 'string', 'max:30'],
            'cv' => [$cvRequired, 'nullable', 'file', 'mimes:pdf', 'max:5120'],
        ];
    }

    private function saveResearcherProfile(Request $request, User $user, array $validated): void
    {
        if (($validated['role'] ?? null) !== 'INVESTIGADOR') {
            return;
        }

        $profile = $user->researcherProfile;
        $profileData = collect($validated)->only([
            'country',
            'salutation',
            'academic_title',
            'profession',
            'research_area',
            'institution',
            'phone',
        ])->all();

        if ($request->hasFile('cv')) {
            if ($profile?->cv_path) {
                Storage::disk('local')->delete($profile->cv_path);
            }

            $profileData['cv_original_name'] = $request->file('cv')->getClientOriginalName();
            $profileData['cv_path'] = $request->file('cv')->store('curricula', 'local');
        }

        $profileData['completed_at'] = now();
        $user->researcherProfile()->updateOrCreate([], $profileData);
    }
}
