<?php

namespace App\Console\Commands;

use App\Models\Barangay;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateStaffUser extends Command
{
    protected $signature = 'app:create-staff';

    protected $description = 'Create a staff account';

    public function handle(): int
    {
        $name = $this->ask('Staff name');
        $email = $this->ask('Staff email');
        $barangayId = $this->ask('Barangay ID');
        $password = $this->secret('Staff password');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'barangay_id' => $barangayId,
            'password' => $password,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'barangay_id' => [
                'required',
                'integer',
                Rule::exists('barangays', 'id'),
            ],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $role = Role::where('name', 'staff')->first();

        if (!$role) {
            $this->error(
                'Staff role not found. Run your seeders first.'
            );

            return self::FAILURE;
        }

        $barangay = Barangay::find($barangayId);

        $user = new User();
        $user->name = $name;
        $user->email = $email;
        $user->password = Hash::make($password);
        $user->barangay()->associate($barangay);
        $user->role()->associate($role);
        $user->save();

        $this->info(
            "Staff account created: {$user->email}"
        );

        return self::SUCCESS;
    }
}
