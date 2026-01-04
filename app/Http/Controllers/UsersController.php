<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UsersController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $search = trim((string) $request->query('q', ''));

        $query = User::query()->orderBy('name');
        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        $users = $query->get();

        return view('users.index', [
            'users' => $users,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('users.create');
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        $roles = $this->roleOptions();

        return view('users.edit', [
            'user' => $user,
            'roles' => $roles,
            'isSelf' => auth()->id() === $user->id,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $data = $this->validatePayload($request, true);
        $password = $data['password'] ?? Str::random(12);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'active' => $request->boolean('active'),
            'password' => Hash::make($password),
        ]);

        $status = 'User created.';
        if ($request->boolean('send_reset_link') || ! isset($data['password'])) {
            $resetStatus = Password::sendResetLink(['email' => $user->email]);
            if ($resetStatus === Password::RESET_LINK_SENT) {
                $status .= ' Password reset link sent.';
            } else {
                $status .= ' Unable to send reset link.';
            }
        }

        return redirect()->route('users.index')->with('status', $status);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $data = $this->validatePayload($request, false, $user);
        $isSelf = $request->user()?->id === $user->id;

        $active = $request->boolean('active');
        if ($isSelf && ! $active) {
            return back()->withErrors([
                'active' => 'You cannot deactivate your own account.',
            ])->withInput();
        }

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'active' => $active,
        ]);

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return redirect()->route('users.index')->with('status', 'User updated.');
    }

    public function sendResetLink(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $status = Password::sendResetLink(['email' => $user->email]);
        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', 'Password reset link sent.');
        }

        return back()->withErrors(['email' => 'Unable to send reset link.']);
    }

    /**
     * @return array<int, string>
     */
    private function roleOptions(): array
    {
        return [
            User::ROLE_OWNER,
            User::ROLE_CASHIER,
            User::ROLE_STOCK_MANAGER,
        ];
    }

    /**
     * @return array{name: string, email: string, role: string, password?: string}
     */
    private function validatePayload(Request $request, bool $isCreate, ?User $user = null): array
    {
        $emailRule = ['required', 'email', 'max:255', 'unique:users,email'];
        if ($user) {
            $emailRule = ['required', 'email', 'max:255', 'unique:users,email,'.$user->id];
        }

        $passwordRules = $isCreate
            ? ['nullable', 'string', 'min:8', 'confirmed']
            : ['nullable', 'string', 'min:8', 'confirmed'];

        return $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => $emailRule,
                'role' => ['required', 'in:'.implode(',', $this->roleOptions())],
                'password' => $passwordRules,
            ],
            [
                'name.required' => 'Enter a name.',
                'email.required' => 'Enter an email.',
                'email.unique' => 'That email is already in use.',
                'role.required' => 'Select a role.',
                'password.min' => 'Password should be at least 8 characters.',
                'password.confirmed' => 'Password confirmation does not match.',
            ]
        );
    }
}
