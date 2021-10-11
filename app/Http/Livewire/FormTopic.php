<?php

namespace App\Http\Livewire;

use Livewire\Component;

class FormTopic extends Component
{
    public $model_name;

    public $step;

    public function mount()
    {
        $this->step = 0;
    }

    public function render()
    {
        return view('livewire.form-topic');
    }

    public function increase()
    {
        dd('OK');
    }

    public function decreaseStep()
    {
        $this->step--;
    }
}
