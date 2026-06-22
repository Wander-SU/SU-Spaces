<?php

use App\Models\TimeSlot;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    //Public Variables
    public $phaseName;
    public $partName;
    public $roomName;
    public $buildingName;
    public $stmbFloor;
    public $start_time_id;
    public $end_time_id;
    public $end_time;
    public $search_date="";

    public function render(){
        if($this->start_time_id==null){
            $this->start_time_id = 50;
            $this->computeEndTimeId();
        }

        if($this->search_date==""){
            $this->search_date=now()->format('Y-m-d');
        }

        $timeSlots = TimeSlot::all();

        return view('components.building-navigation.⚡building-navigation',compact('timeSlots'));
    }

    /**
     * Functions for selection of buildings and rooms
    */
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

    /**
     * Listen for changes on start time id
    */
    public function updatedStartTimeId(){
        $this->computeEndTimeId();
    }

   /**
    * Change the end time id.
    */ 
    public function computeEndTimeId(){
        $this->end_time_id = $this->start_time_id + 3;
        $this->end_time = TimeSlot::findOrFail($this->end_time_id)->end_time;
    }
};
?>

{{-- Parent Div for Livewire --}}
<div>
    {{-- Show the messages --}}
    @if (session()->has('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    @if (session()->has('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <div class="card-info">
        <div class="card-header">
            {{-- Buttons to Cancel --}}
            <div class="d-inline-block me-2 rounded-2">
                @if($phaseName!=null)
                    <a href="#" wire:click="backToBirdView"
                    class="btn btn-danger"
                    >
                        Back to General View
                    </a>
                @endif
                
                @if($partName!=null || $buildingName!=null)
                    <a href="#" wire:click="backToPhaseView"
                    class="btn btn-danger"
                    >
                        Back to Phase View
                    </a>
                @endif

                @if($stmbFloor!=null)
                    <a href="#" wire:click="backToStmbView"
                    class="btn btn-danger"
                    >
                        Back to STMB View
                    </a>
                @endif


                @if($this->roomName!=null)
                <form class="mt-4 inline-block rounded-2">
                    <input type="text" disabled wire:model='roomName' value={{ old('roomName') }}>
                </form>
                @endif
            </div>
            <div class="card-tools">
                {{-- Search Date form --}}
                <form class="d-inline-block me-2">
                    <div class="input-group input-group-sm">
                        {{--  show inline error messages --}}
                        <label for="search_date" class="me-2">Search Date:</label>
                        <input wire:model.live.debounce.100ms="search_date" type="date" name="search_date"
                        class="form-control {{ $errors->has('search_date') ? 'is-invalid' : '' }}" value="{{ old('search_date') }}">
                        @error('search_date')
                            <div class="invalid-feedback">
                            {{ $message }}
                            </div>
                        @enderror
                    </div>
                </form>

                <form class="d-inline-block me-2">
                    <div class="input-group input-group-sm">
                        {{--  show inline error messages --}}
                        <label for="start_time" class="me-2">Start:</label>
                        <select wire:model.live.debounce.100ms="start_time_id" type="time" name="start_time_id"
                        class="form-control {{ $errors->has('start_time_id') ? 'is-invalid' : '' }}" value="{{ old('start_time_id') }}">
                            @foreach($timeSlots as $timeSlot)
                                @if($timeSlot->start_time>="07:00:00" && $timeSlot->end_time<="21:00:00" && $timeSlot->end_time!="00:00:00" && ($timeSlot->id-2)%4==0)
                                    <option value="{{ $timeSlot->id }}">{{$timeSlot->start_time}}</option>
                                @endif
                            @endforeach
                        </select>
                        @error('search_date')
                            <div class="invalid-feedback">
                            {{ $message }}
                            </div>
                        @enderror
                    </div>
                </form>

                <form class="d-inline-block me-2">
                    <div class="input-group input-group-sm">
                        {{--  show inline error messages --}}
                        <label for="end _time" class="me-2">End:</label>
                        <select wire:model="end_time_id" type="time" name="start_time_id"
                        class="form-control {{ $errors->has('end_time_id') ? 'is-invalid' : '' }}" disabled value="{{ old('end_time_id') }}">
                            <option>{{$end_time}}</option>
                        </select>
                        @error('search_date')
                            <div class="invalid-feedback">
                            {{ $message }}
                            </div>
                        @enderror
                    </div>
                </form>


            </div>
        </div>
        <div class="card-body">
            <div class="svg-scroll-container border overflow-auto" style="">
                @if($phaseName==null)
                    <livewire:building-navigation.bird-eye-view.bird-eye-view/>
                @endif
                {{-- End of Birds Eye View --}}

                @if($phaseName=="Phase1")
                {{-- Button to Reset to Bird Eye View --}}
                    @if($partName==null)
                        <livewire:building-navigation.phase1.phase1-view/>
                        {{-- End of Phase 1 General View --}}
                    @elseif ($partName=="RightWing")
                        <livewire:building-navigation.phase1.phase1-parts.right-wing/>
                        {{-- End of Phase 1 Right Wing --}}
                    @elseif ($partName=="CentralPart")
                        <livewire:building-navigation.phase1.phase1-parts.central-part/>
                        {{-- End of Phase 1 Central Part --}}
                    @elseif ($partName=="LeftWing")
                        <livewire:building-navigation.phase1.phase1-parts.left-wing/>
                        {{-- End of Phase 1 Left Wing --}}
                    @elseif ($partName=="CentralPartFirstFloor")
                        <livewire:building-navigation.phase1.phase1-parts.central-part-floor-1/>
                        {{-- End of Phase 1 Left Wing --}}
                    @endif
                    {{-- End Of Phase 1 parts --}}
                @endif
                {{-- End Of Phase 1 Diagrams --}}

                @if($phaseName=="Phase2")
                    @if($buildingName==null)
                        <livewire:building-navigation.phase2.phase2-view/>
                        {{-- End of Phase 2 General View --}}
                    @elseif($buildingName=="MSB")
                        <livewire:building-navigation.phase2.phase2-buildings.msb-view/>
                        {{-- End of MSB View --}}
                    @elseif($buildingName=="OvalBuilding")
                        <livewire:building-navigation.phase2.phase2-buildings.ovb-view/>
                        {{-- End of Oval Building View --}}
                    @elseif($buildingName=="Library")
                        <livewire:building-navigation.phase2.phase2-buildings.unilib-view/>
                        {{-- End of Library View --}}
                    @elseif($buildingName=="Forge")
                        <livewire:building-navigation.phase2.phase2-buildings.forge-view/>
                        {{-- End of Forge View --}}
                    @elseif($buildingName=="SBS")
                        <livewire:building-navigation.phase2.phase2-buildings.sbs-view/>
                        {{-- End of SBS View --}}
                    @elseif($buildingName=="STC")
                        <livewire:building-navigation.phase2.phase2-buildings.stc-view/>
                        {{-- End of STC View --}}
                    @elseif($buildingName=="STMB")
                        @if($stmbFloor==null)
                            <livewire:building-navigation.phase2.phase2-buildings.stmb-view/>
                            {{-- End of STMB View --}}
                        @elseif($stmbFloor=="Basement")
                            <livewire:building-navigation.phase2.stmb-floors.stmb-basement/>
                            {{-- End of STMB Basement view --}}
                        @elseif($stmbFloor=="GF")
                            <livewire:building-navigation.phase2.stmb-floors.stmb-gf/>
                            {{-- End of STMB Ground Floor view --}}
                        @elseif($stmbFloor=="F1")
                            <livewire:building-navigation.phase2.stmb-floors.stmb-f1/>
                            {{-- End of STMB Floor 1 view --}}
                        @elseif($stmbFloor=="F2")
                            <livewire:building-navigation.phase2.stmb-floors.stmb-f2/>
                            {{-- End of STMB Floor 2 view --}}
                        @elseif($stmbFloor=="F5")
                            <livewire:building-navigation.phase2.stmb-floors.stmb-f5/>
                            {{-- End of STMB Floor 5 view --}}
                        @endif
                    @endif

                @endif
                {{-- End Of Phase 2 Diagrams --}}
            </div>
        </div>
    </div>
</div>

 