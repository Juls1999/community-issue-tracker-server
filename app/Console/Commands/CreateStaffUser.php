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
        $first_name = $this->ask('Staff first name');
        $middle_name = $this->ask('Staff middle name');
        $last_name = $this->ask('Staff last name');
        $phone_number = $this->ask('staff phone number');
        $email = $this->ask('Staff email');
        $barangayId = $this->ask('Barangay ID');
        $password = $this->secret('Staff password');

        $validator = Validator::make([
            'first_name' => $first_name,
            'middle_name' => $middle_name,
            'last_name' => $last_name,
            'phone_number' => $phone_number,
            'email' => $email,
            'barangay_id' => $barangayId,
            'password' => $password,
        ], [
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['sometimes', 'string', 'max:255', 'nullable'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['sometimes', 'string', 'max:255', 'nullable'],
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
        $user->first_name = $first_name;
        $user->middle_name = $middle_name;
        $user->last_name = $last_name;
        $user->phone_number = $phone_number;
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
