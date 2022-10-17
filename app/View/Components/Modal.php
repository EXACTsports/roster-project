<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Modal extends Component
{
    public $name;
    public $clickAwayCloses;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($name, $clickAwayCloses = true)
    {
        $this->name = $name;
        $this->clickAwayCloses = $clickAwayCloses;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.modal');
    }
}
