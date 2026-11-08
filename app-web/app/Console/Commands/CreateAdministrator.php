<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CreateAdministrator extends Command
{
    protected $signature = 'app:create-admin';
    protected $description = 'Crear una cuenta administrativa sin contraseñas predeterminadas ni argumentos sensibles';

    public function handle(): int
    {
        $data = [
            'name' => $this->ask('Nombre'),
            'email' => Str::lower(trim((string) $this->ask('Correo'))),
            'role' => $this->choice('Rol', ['owner', 'administrator', 'editor'], 0),
            'password' => $this->secret('Contraseña (mínimo 12 caracteres, letras y números)'),
            'password_confirmation' => $this->secret('Repite la contraseña'),
        ];
        $validator = Validator::make($data, [
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:254|unique:users,email',
            'role' => [Rule::enum(Role::class)],
            'password' => ['required', 'confirmed', 'max:255', Password::min(12)->letters()->numbers()],
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }
        DB::transaction(function () use ($data) {
            $user = new User();
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->password = $data['password'];
            $user->role = Role::from($data['role']);
            $user->save();
            AuditEvent::create(['user_id' => $user->id, 'action' => 'user.created', 'metadata' => ['via' => 'console']]);
        });
        $this->info('Cuenta creada. Accede a /admin con tu correo y contraseña.');

        return self::SUCCESS;
    }
}
