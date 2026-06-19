<?php

use Livewire\Component;
use Livewire\Attributes\On;

new class extends Component
{
    //Public Variables
    public $phaseName;
    public $partName;
    public $roomName;
    public $buildingName;
    public $stmbFloor;

    #[On('phaseSelected')]
    public function phaseSelected($phaseName){
        $this->phaseName = $phaseName;
    }

    #[On('partSelected')]
    public function partSelected($partName){
        $this->partName= $partName;
    }

    #[On('roomSelected')]
    public function roomSelected($roomName){
        $this->roomName= $roomName;
    }

    #[On('buildingSelected')]
    public function buildingSelected($buildingName){
        $this->buildingName=$buildingName;
    }

    #[On('stmbFloorSelected')]
    public function stmbFloorSelected($stmbFloor){
        $this->stmbFloor=$stmbFloor;
    }

    /**
     * Removes the current phase that is selected
    */
    public function backToBirdView(){
        $this->phaseName=null;
        $this->buildingName=null;
        $this->partName=null;
        $this->roomName=null;
        $this->stmbFloor=null;
    }

    /**
     * Removes the current part that is selected
    */
    public function backToPhaseView(){
        $this->buildingName=null;
        $this->partName=null;
        $this->roomName=null;
        $this->stmbFloor=null;
    }

    /**
     * Removes the current floor of STMB
    */
    public function backToStmbView(){
        $this->stmbFloor=null;
    }
};
?>

{{-- Parent Div for Livewire --}}
<div>
    @if($this->roomName!=null)
        <form class="mt-4">
            <input type="text" disabled wire:model='roomName' value={{ old('roomName') }}>
        </form>
    @endif
    <div class="svg-scroll-container border overflow-auto" style="">  
        {{-- Buttons to Cancel --}}
        <div class="inline-block z-3 position-absolute top-0 start-0 rounded-2">
            @if($phaseName!=null)
                <a href="#" wire:click="backToBirdView"
                class="btn btn-danger m-2"
                >
                    Back to General View
                </a>
            @endif
            
            @if($partName!=null || $buildingName!=null)
                <a href="#" wire:click="backToPhaseView"
                class="btn btn-danger m-2 "
                >
                    Back to Phase View
                </a>
            @endif

            @if($stmbFloor!=null)
                <a href="#" wire:click="backToStmbView"
                class="btn btn-danger m-2 "
                >
                    Back to STMB View
                </a>
            @endif
        </div>

        @if($phaseName==null)
            <x-building-navigation.bird-eye-view.bird-eye-view/>
        @endif
        {{-- End of Birds Eye View --}}

        @if($phaseName=="Phase1")
        {{-- Button to Reset to Bird Eye View --}}
            @if($partName==null)
                <x-building-navigation.phase1.phase1-view/>
                {{-- End of Phase 1 General View --}}
            @elseif ($partName=="RightWing")
                <x-building-navigation.phase1.phase1-parts.right-wing/>
                {{-- End of Phase 1 Right Wing --}}
            @elseif ($partName=="CentralPart")
                <x-building-navigation.phase1.phase1-parts.central-part/>
                {{-- End of Phase 1 Central Part --}}
            @elseif ($partName=="LeftWing")
                <x-building-navigation.phase1.phase1-parts.left-wing/>
                {{-- End of Phase 1 Left Wing --}}
            @elseif ($partName=="CentralPartFirstFloor")
                <x-building-navigation.phase1.phase1-parts.central-part-floor-1/>
                {{-- End of Phase 1 Left Wing --}}
            @endif
            {{-- End Of Phase 1 parts --}}
        @endif
        {{-- End Of Phase 1 Diagrams --}}

        @if($phaseName=="Phase2")
            @if($buildingName==null)
                <x-building-navigation.phase2.phase2-view/>
                {{-- End of Phase 2 General View --}}
            @elseif($buildingName=="MSB")
                <x-building-navigation.phase2.phase2-buildings.msb-view/>
                {{-- End of MSB View --}}
            @elseif($buildingName=="OvalBuilding")
                <x-building-navigation.phase2.phase2-buildings.ovb-view/>
                {{-- End of Oval Building View --}}
            @elseif($buildingName=="Library")
                <x-building-navigation.phase2.phase2-buildings.unilib-view/>
                {{-- End of Library View --}}
            @elseif($buildingName=="Forge")
                <x-building-navigation.phase2.phase2-buildings.forge-view/>
                {{-- End of Forge View --}}
            @elseif($buildingName=="SBS")
                <x-building-navigation.phase2.phase2-buildings.sbs-view/>
                {{-- End of SBS View --}}
            @elseif($buildingName=="STC")
                <x-building-navigation.phase2.phase2-buildings.stc-view/>
                {{-- End of STC View --}}
            @elseif($buildingName=="STMB")
                @if($stmbFloor==null)
                    <x-building-navigation.phase2.phase2-buildings.stmb-view/>
                    {{-- End of STMB View --}}
                @elseif($stmbFloor=="Basement")
                    <x-building-navigation.phase2.stmb-floors.stmb-basement/>
                    {{-- End of STMB Basement view --}}
                @elseif($stmbFloor=="GF")
                    <x-building-navigation.phase2.stmb-floors.stmb-gf/>
                    {{-- End of STMB Ground Floor view --}}
                @elseif($stmbFloor=="F1")
                    <x-building-navigation.phase2.stmb-floors.stmb-f1/>
                    {{-- End of STMB Floor 1 view --}}
                @elseif($stmbFloor=="F2")
                    <x-building-navigation.phase2.stmb-floors.stmb-f2/>
                    {{-- End of STMB Floor 2 view --}}
                @elseif($stmbFloor=="F5")
                    <x-building-navigation.phase2.stmb-floors.stmb-f5/>
                    {{-- End of STMB Floor 5 view --}}
                @endif
            @endif

        @endif
        {{-- End Of Phase 2 Diagrams --}}
    </div>
</div>

 