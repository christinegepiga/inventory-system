<?php

namespace App\Livewire;

// app/Livewire/ToastNotification.php

use Livewire\Component;

class ToastNotification extends Component
{
    public $show = false;
    public $message = '';
    public $type = 'success';
    public $duration = 3000;

    protected $listeners = ['showToast' => 'show'];

    public function show($type, $message, $duration = null)
    {
        $this->type = $type;
        $this->message = $message;
        $this->duration = $duration ?? $this->duration;
        $this->show = true;
        
        $this->dispatch('toast-shown');
    }

    public function hide()
    {
        $this->show = false;
    }

    public function render()
    {
        return view('livewire.toast-notification');
    }
}
