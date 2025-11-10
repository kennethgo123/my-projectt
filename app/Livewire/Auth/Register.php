<?php

namespace App\Livewire\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Illuminate\Auth\Events\Registered;

class Register extends Component
{
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $selectedRole = '';
    public $roles = [];
    public $agreeTerms = false;

    public function mount()
    {
        $this->roles = Role::whereIn('name', ['client', 'lawyer', 'law_firm'])->get();
    }

    public function rules()
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            // Password: at least 12 chars, 1 uppercase, 1 number, 1 special char
            'password' => [
                'required',
                'string',
                'min:12',
                'regex:/[A-Z]/',          // at least 1 uppercase
                'regex:/[0-9]/',          // at least 1 number
                'regex:/[@$!%*#?&^()_+\\-={}\\[\\]:;"\'<>,.~`|\\\\\\/]/', // at least 1 special
                'confirmed'
            ],
            'selectedRole' => ['required', 'exists:roles,id'],
            'agreeTerms' => ['required', 'accepted'],
        ];
    }

    public function register()
    {
        $this->validate();

        $user = User::create([
            'name' => 'User', // Default name that will be updated during profile completion
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role_id' => $this->selectedRole,
            'status' => 'pending',
            'profile_completed' => false,
        ]);

        event(new Registered($user));

        $message = 'Your email verification has been sent! Kindly check your inbox and verify for us to know if it\'s you';
        session()->flash('message', $message);
        session()->flash('status', $message);
        
        return redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.auth.register')
            ->layout('layouts.guest');
    }
} 