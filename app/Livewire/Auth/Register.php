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
            // Base rules; complexity enforced manually for specific messages
            'password' => [
                'required',
                'string',
                'min:12',
                'confirmed',
            ],
            'selectedRole' => ['required', 'exists:roles,id'],
            'agreeTerms' => ['required', 'accepted'],
        ];
    }

    private function validatePasswordComplexity(): bool
    {
        $this->resetErrorBag('password');
        $pass = (string) $this->password;
        if (!preg_match('/[A-Z]/', $pass)) {
            $this->addError('password', 'Please enter at least 1 uppercase letter.');
            return false;
        }
        if (!preg_match('/[0-9]/', $pass)) {
            $this->addError('password', 'Please enter at least 1 number.');
            return false;
        }
        if (!preg_match('/[^a-zA-Z0-9]/', $pass)) {
            $this->addError('password', 'Please enter at least 1 special character.');
            return false;
        }
        return true;
    }

    public function updatedPassword()
    {
        // Give immediate specific feedback while typing
        if (strlen((string) $this->password) >= 1) {
            $this->validateOnly('password');
            $this->validatePasswordComplexity();
        }
    }

    public function register()
    {
        $this->validate();
        if (!$this->validatePasswordComplexity()) {
            return;
        }
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