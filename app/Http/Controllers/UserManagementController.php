<?php


namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    public function index()
    {
        $roles = Role::orderBy('id')->get(['id','name']);
        return view('users.index', compact('roles'));
    }

    public function data()
    {
        $actor = auth()->user();

        $users = User::with('role:id,name')
            ->orderBy('name')
            ->get(['id','name','email','role_id','status','created_at'])
            ->map(function ($u) use ($actor) {
                $editable = !($actor->role_id === Role::ADMIN && $u->role_id === Role::SUPER_ADMIN);
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'role_id' => $u->role_id,
                    'role_name' => $u->role?->name,
                    'status' => (int)$u->status, // ← cambiar a int en lugar de bool
                    'created_at' => $u->created_at?->format('Y-m-d'),
                    'editable' => $editable,
                ];
            });

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $this->authorize('manage-users');

        $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255', 'unique:users,email'],
            'password' => ['required','string','min:8'],
            'role_id' => ['required', Rule::in([Role::ADMIN, Role::PROVEEDOR, Role::USUARIO, Role::SUPER_ADMIN])],
            'status' => ['required','boolean'],
        ]);

        // Si es admin, no puede crear super admin
        if (auth()->user()->role_id === Role::ADMIN && (int)$request->role_id === Role::SUPER_ADMIN) {
            return response()->json(['ok'=>false,'message'=>'No autorizado para crear Super Admin.'], 403);
        }

        $user = User::create([
            'name' => $request->name,
            'email'=> $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'status' => $request->boolean('status'),
        ]);

        return response()->json(['ok'=>true,'message'=>'Usuario creado.','user'=>$user]);
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('manage-users');

        $request->validate([
            'name' => ['sometimes','required','string','max:255'],
            'email' => ['sometimes','required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'password' => ['nullable','string','min:8'],
            'role_id' => ['sometimes','required', Rule::in([Role::ADMIN, Role::PROVEEDOR, Role::USUARIO, Role::SUPER_ADMIN])],
            'status' => ['sometimes','integer','in:0,1'],
        ]);

        $actor = auth()->user();

        if ($actor->role_id === Role::ADMIN && $user->role_id === Role::SUPER_ADMIN) {
            return response()->json(['ok'=>false,'message'=>'No autorizado para modificar Super Admin.'], 403);
        }
        if ($actor->role_id === Role::ADMIN && $request->has('role_id') && (int)$request->role_id === Role::SUPER_ADMIN) {
            return response()->json(['ok'=>false,'message'=>'No autorizado para asignar rol Super Admin.'], 403);
        }

        $data = [];
        if ($request->has('name')) $data['name'] = $request->name;
        if ($request->has('email')) $data['email'] = $request->email;
        if ($request->has('role_id')) $data['role_id'] = (int)$request->role_id;
        if ($request->has('status')) {
            $data['status'] = (int)$request->input('status');
        }
        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        
        $user->update($data);
        $user->refresh();

        return response()->json([
            'ok' => true,
            'message' => 'Usuario actualizado correctamente.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role_id' => $user->role_id,
                'status' => (int)$user->status,
            ]
        ]);
    }
}
