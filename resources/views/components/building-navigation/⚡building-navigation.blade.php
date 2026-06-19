<?php

use Livewire\Component;
use Livewire\Attributes\On;

new class extends Component
{
    //Public Variables
    public $phaseName;

    #[On('phaseSelected')]
    public function phaseSelected($phaseName){
        $this->phaseName = $phaseName;
    }
};
?>



{{-- Parent Div for Livewire --}}
<div>
    <div class="svg-scroll-container border overflow-auto" style="">
        @if($phaseName==null)
            <x-building-navigation.bird-eye-view.bird-eye-view/>
        @endif

        @if($phaseName=="Phase1")
            <x-building-navigation.phase1.phase1-view/>
        @endif

        @if($phaseName=="Phase2")
            <x-building-navigation.phase2.phase2-view/>
        @endif
    </div>
</div>

 