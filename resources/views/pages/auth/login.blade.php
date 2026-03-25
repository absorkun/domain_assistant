<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component {
    #[Validate('required|string')]
    public string $name = '';

    #[Validate('required|string')]
    public string $password = '';

    public function submit(): void
    {
        $this->validate();

        if (! Auth::attempt(['name' => $this->name, 'password' => $this->password])) {
            throw ValidationException::withMessages([
                'name' => 'Invalid credentials',
            ]);
        }

        session()->regenerate();

        $this->redirect('/domain', navigate: true);
    }
};
?>

<x-ui.auth-shell title="Masuk ke akun" subtitle="Login">
    <form wire:submit="submit" class="d-grid gap-3">
        <div>
            <label class="form-label">Username</label>
            <input type="text" wire:model="name" class="form-control">
            @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        <div>
            <label class="form-label">Password</label>
            <input type="password" wire:model="password" class="form-control">
            @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn btn-primary btn-lg">Masuk</button>
    </form>
</x-ui.auth-shell>
