<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public const ADMIN = 1;
    public const USUARIO = 2;
    public const PROVEEDOR = 3;
    public const SUPER_ADMIN = 4;

    protected $fillable = ['name', 'display_name', 'badge_class'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return match($this->name) {
            'super_admin' => 'Super Admin',
            'admin' => 'Administrador',
            'proveedor' => 'Proveedor',
            'usuario' => 'Usuario',
            default => ucfirst(str_replace('_',' ', $this->name)),
        };
    }

    public function getBadgeClassAttribute(): string
    {
        return match($this->id) {
            self::SUPER_ADMIN => 'role-super',
            self::ADMIN => 'role-admin',
            self::PROVEEDOR => 'role-proveedor',
            self::USUARIO => 'role-usuario',
            default => 'role-default',
        };
    }
}
