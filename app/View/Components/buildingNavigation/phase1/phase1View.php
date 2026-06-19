<?php

namespace App\View\Components\buildingNavigation\phase1;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class phase1View extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.building-navigation.phase1.phase1-view');
    }
}
