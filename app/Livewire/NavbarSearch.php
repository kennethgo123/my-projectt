<?php

namespace App\Livewire;

use Livewire\Component;

class NavbarSearch extends Component
{
    // We're now using a standard HTML form, so we don't need to track the search value
    // or handle the search method in Livewire anymore
    
    public function render()
    {
        return view('livewire.navbar-search');
    }
}
