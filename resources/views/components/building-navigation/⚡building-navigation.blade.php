<?php

use App\Traits\Concerns\ComputeStatuses;
use App\Models\TimeSlot;
use App\Models\Room;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\Building;
use Illuminate\Support\Facades\Cache;

new class extends Component
{
    // Inherited functionality from the trait (Multiple Inheritance not found in php)
    use ComputeStatuses;

    //Public Variables
    public array $roomStatuses = [];
    public $phaseName;
    public $partName;
    public $roomName;
    public $room_id;
    public $room_name;
    public $buildingName;
    public $building_id;
    public $building_name;
    public $room_capacity;
    public $number_occupants;
    public $book_date;
    public $stmbFloor;
    public $start_time_id;
    public $end_time_id;
    public $end_time;
    public $search_date="";

    public function mount(){
        if($this->start_time_id==null){
            $this->start_time_id = 50;
            $this->computeEndTimeId();
        }

        if($this->search_date==""){
            $this->search_date=now()->format('Y-m-d');
        }

        $this->computeStatuses();
    }

    public $flashType = null;
    public $flashMessage = null;

    #[On('bookingMessage')]
    public function bookingMessage($type, $message)
    {
        $this->flashType = $type;
        $this->flashMessage = $message;
    }

    public function render(){
        $timeSlots = TimeSlot::query()
            ->select('id', 'start_time', 'end_time')
            ->orderBy('id')
            ->get();

        $roomLookup = Cache::remember('ui:room-lookup:name-capacity', now()->addMinutes(10), function () {
            return Room::query()
                ->select('id', 'room_name', 'capacity')
                ->get()
                ->mapWithKeys(fn ($room) => [
                    (string) $room->id => [
                        'room_name' => $room->room_name,
                        'capacity' => (string) $room->capacity,
                    ],
                ])
                ->toArray();
        });

        return view('components.building-navigation.⚡building-navigation',compact('timeSlots', 'roomLookup'));
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
    public function roomSelected($roomName,$room_id){
        $this->roomName= $roomName;
        $this->room_id= $room_id;
        $this->showBookForm();

    }

    public function showBookForm()
    {
        if(auth()->user()->role->role_name!="Student"){
            $privilegedBook=True;
        }
        else{
            $privilegedBook = False;
        }
        $room = Room::findOrFail($this->room_id);
        $building = Building::findOrFail($room->building_id);
        $this->building_id = $building->id;
        $this->building_name = $building->building_name;
        $this->room_capacity = $room->capacity;
        $this->room_id = $room->id;
        $this->room_name = $room->room_name;
        if(auth()->user()->role->role_name!="Student"){
            $this->number_occupants = $room->capacity;
        }
        else{
            $this->number_occupants = 1;
        }
        $this->book_date = $this->search_date;
        $this->dispatch('initiateShowFormFromNav',[
            'showForm' => True,
            'isPrivilegedBook' => $privilegedBook,
            'building_id'=>$this->building_id,
            'building_name'=>$this->building_name,
            'room_capacity' => $this->room_capacity,
            'room_id' => $this->room_id,
            'room_name' => $this->room_name,
            'book_date' => $this->book_date,
            'number_occupants' => $this->number_occupants,
            'start_time_id' => $this->start_time_id,
            'end_time_id' => $this->end_time_id,
            'search_date'=>$this->search_date
      ]);
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
        $this->dispatch('initiatedHideForm');
    }

    /**
     * Removes the current part that is selected
    */
    public function backToPhaseView(){
        $this->buildingName=null;
        $this->partName=null;
        $this->roomName=null;
        $this->stmbFloor=null;
        $this->dispatch('initiatedHideForm');
    }

    /**
     * Removes the current floor of STMB
    */
    public function backToStmbView(){
        $this->stmbFloor=null;
        $this->dispatch('initiatedHideForm');
    }

    /**
     * Listen for changes on start time id
    */
    public function updatedStartTimeId(){
        $this->computeEndTimeId();
        $this->computeStatuses();
    }

    public function updatedEndTimeId(){
        $this->computeStatuses();
    }

    public function updatedSearchDate(){
        $this->computeStatuses();
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
<div
    x-data="roomOverlayState(@js($roomLookup), @js($roomStatuses))"
    x-init="init()"
>
    @if ($flashMessage)
        <div class="alert alert-{{ $flashType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ $flashMessage }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" wire:click="$set('flashMessage', null)"></button>
        </div>
    @endif

    {{-- Form for Booking in right-side drawer --}}
    <div
        x-show="formOpen"
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed right-2 sm:right-4 top-[4.5rem] bottom-[3.75rem] w-[calc(100vw-1rem)] sm:w-full sm:max-w-md bg-[#02338D] backdrop-blur-md border border-white/10 rounded-xl sm:rounded-2xl shadow-2xl z-50 p-6 overflow-y-auto text-white flex flex-col justify-between"
        style="display: none;"
    >
        <livewire:book-forms.book-form/>
    </div>

    <div class="card-info rounded-2xl border border-[#1d2d54]/10 bg-white/45 backdrop-blur-md shadow-xl overflow-hidden">
        <div class="card-header border-0 bg-transparent pb-0">
            <div class="d-flex flex-column gap-3">
            {{-- Buttons to Cancel --}}
            <div class="d-flex flex-wrap gap-2 rounded-2">
                @if($phaseName!=null)
                    <a href="#" wire:click="backToBirdView"
                    class="bg-[#941c1c] text-white hover:bg-gradient-to-r hover:from-[#F11D22] hover:to-[#FFCC00] hover:text-[#1b1b18] transition-colors text-sm font-medium py-2 px-4 rounded-lg"
                    >
                        Back to General View
                    </a>
                @endif
                
                @if($partName!=null || $buildingName!=null)
                    <a href="#" wire:click="backToPhaseView"
                    class="bg-[#941c1c] text-white hover:bg-gradient-to-r hover:from-[#F11D22] hover:to-[#FFCC00] hover:text-[#1b1b18] transition-colors text-sm font-medium py-2 px-4 rounded-lg"
                    >
                        Back to Phase View
                    </a>
                @endif

                @if($stmbFloor!=null)
                    <a href="#" wire:click="backToStmbView"
                    class="bg-[#941c1c] text-white hover:bg-gradient-to-r hover:from-[#F11D22] hover:to-[#FFCC00] hover:text-[#1b1b18] transition-colors text-sm font-medium py-2 px-4 rounded-lg"
                    >
                        Back to STMB View
                    </a>
                @endif

            </div>
            <div class="d-flex flex-nowrap gap-3 align-items-end justify-content-end ms-auto">
                {{-- Search Date form --}}
                <form class="m-0">
                    <div class="d-flex flex-row gap-2 align-items-center">
                        {{--  show inline error messages --}}
                        <label for="search_date" class="mb-0 text-sm text-[#1b1b18]">Search Date:</label>
                        <input wire:model.live.debounce.100ms="search_date" type="date" name="search_date"
                        class="w-[180px] rounded-lg border border-[#1d2d54]/20 bg-white px-3 py-2 text-sm text-[#1b1b18] focus:outline-none {{ $errors->has('search_date') ? 'is-invalid' : '' }}" value="{{ old('search_date') }}">
                        @error('search_date')
                            <div class="invalid-feedback">
                            {{ $message }}
                            </div>
                        @enderror
                    </div>
                </form>

                <form class="m-0">
                    <div class="d-flex flex-row gap-2 align-items-center">
                        {{--  show inline error messages --}}
                        <label for="start_time" class="mb-0 text-sm text-[#1b1b18]">Start:</label>
                        <select wire:model.live.debounce.100ms="start_time_id" type="time" name="start_time_id"
                        class="w-[140px] rounded-lg border border-[#1d2d54]/20 bg-white px-3 py-2 text-sm text-[#1b1b18] focus:outline-none {{ $errors->has('start_time_id') ? 'is-invalid' : '' }}" value="{{ old('start_time_id') }}">
                            @foreach($timeSlots as $timeSlot)
                                @if($timeSlot->start_time>="07:00:00" && $timeSlot->end_time<="21:00:00" && $timeSlot->end_time!="00:00:00")
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

                <form class="m-0">
                    <div class="d-flex flex-row gap-2 align-items-center">
                        {{--  show inline error messages --}}
                        <label for="end _time" class="mb-0 text-sm text-[#1b1b18]">End:</label>
                        <select wire:model.live.debounce.100ms="end_time_id" type="time" name="end_time_id"
                        class="w-[140px] rounded-lg border border-[#1d2d54]/20 bg-white px-3 py-2 text-sm text-[#1b1b18] focus:outline-none {{ $errors->has('end_time_id') ? 'is-invalid' : '' }}" value="{{ old('end_time_id') }}">
                            @foreach($timeSlots as $timeSlot)
                                @if($timeSlot->start_time>="07:00:00" && $timeSlot->end_time<="21:00:00" && $timeSlot->end_time!="00:00:00" && ($timeSlot->id-$start_time_id)>=0 && ($timeSlot->id-$start_time_id)<=12)
                                    <option value="{{ $timeSlot->id }}">{{$timeSlot->end_time}}</option>
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

            </div>
            </div>
        </div>
        <div class="card-body pt-3">
            <div class="svg-scroll-container overflow-auto rounded-xl border border-[#1d2d54]/15 bg-white/55 backdrop-blur-sm p-2 sm:p-3 shadow-inner">
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

@once
<style>
    .svg-scroll-container g.room rect {
        transition: fill .18s ease, stroke .18s ease, stroke-width .18s ease;
        transform: none !important;
    }

    .svg-scroll-container g.room:hover rect,
    .svg-scroll-container g.room:focus-within rect {
        fill: var(--room-hover-fill, var(--room-base-fill, #f0fdf4)) !important;
        stroke: var(--room-hover-stroke, var(--room-base-stroke, #86efac)) !important;
        stroke-width: 2 !important;
    }
</style>
<script>
function roomOverlayState(roomLookup, statuses) {
    return {
        roomLookup: roomLookup ?? {},
        statuses: statuses ?? {},
        formOpen: false,
        selectedRoom: '',
        listenersBound: false,
        observer: null,
        annotateQueued: false,

        init() {
            this.bindDrawerListeners();
            this.scheduleAnnotate();
            this.watchRoomMapChanges();
        },

        bindDrawerListeners() {
            if (this.listenersBound || !window.Livewire) {
                return;
            }

            this.listenersBound = true;

            window.Livewire.on('roomSelected', (payload) => {
                const data = Array.isArray(payload) ? (payload[0] ?? {}) : (payload ?? {});
                if (data.roomName) {
                    this.selectedRoom = data.roomName;
                }
                this.formOpen = true;
            });

            window.Livewire.on('initiateShowFormFromNav', (payload) => {
                const data = Array.isArray(payload) ? (payload[0] ?? {}) : (payload ?? {});
                if (data.room_name) {
                    this.selectedRoom = data.room_name;
                }
                this.formOpen = true;
            });

            window.Livewire.on('initiatedHideForm', () => {
                this.formOpen = false;
            });
        },

        watchRoomMapChanges() {
            const container = this.$root.querySelector('.svg-scroll-container');
            if (!container) {
                return;
            }

            if (this.observer) {
                this.observer.disconnect();
            }

            this.observer = new MutationObserver(() => this.scheduleAnnotate());
            this.observer.observe(container, { childList: true, subtree: true });
        },

        scheduleAnnotate() {
            if (this.annotateQueued) {
                return;
            }

            this.annotateQueued = true;
            requestAnimationFrame(() => {
                this.annotateQueued = false;
                this.annotateRooms();
            });
        },

        annotateRooms() {
            const rooms = this.$root.querySelectorAll('.svg-scroll-container g.room');
            rooms.forEach((group) => {
                const rect = group.querySelector('rect');
                if (!rect) {
                    return;
                }

                group.querySelectorAll('.room-overlay-label').forEach((node) => node.remove());

                const clickExpr = group.getAttribute('@click') || group.getAttribute('x-on:click') || '';
                const parsed = clickExpr.match(/roomName:'([^']+)'(?:,room_id:(\d+))?/);
                if (!parsed) {
                    return;
                }

                const dispatchRoomName = parsed[1];
                const roomId = parsed[2] ? String(parsed[2]) : null;
                const dbRoom = roomId ? this.roomLookup[roomId] : null;

                const frame = this.getLabelFrame(group, rect);
                const x = frame.x;
                const y = frame.y;
                const width = frame.width;
                const height = frame.height;
                if (!width || !height) {
                    return;
                }

                const styleExpr = rect.getAttribute('style') || '';
                const roomColorExpr = styleExpr.match(/roomColor\('([^']+)'\)/);
                const statusKey = roomColorExpr ? roomColorExpr[1] : dispatchRoomName;
                const mapContext = this.getMapContext(group);

                const rawStatus = this.resolveRoomStatus({
                    statusKey,
                    dispatchRoomName,
                    roomId,
                    dbRoomName: dbRoom?.room_name,
                });
                const state = this.normalizeState(rawStatus);
                const colors = this.stateColors(state);
                const surface = this.stateSurface(state);
                const hoverSurface = this.stateHoverSurface(state);

                // Keep room vector palette consistent with the blueprint tokens.
                rect.style.fill = surface.fill;
                rect.style.stroke = surface.stroke;
                rect.style.strokeWidth = '1.5';
                group.style.setProperty('--room-base-fill', surface.fill);
                group.style.setProperty('--room-base-stroke', surface.stroke);
                group.style.setProperty('--room-hover-fill', hoverSurface.fill);
                group.style.setProperty('--room-hover-stroke', hoverSurface.stroke);

                const roomName = this.resolveRoomName({ group, dispatchRoomName, roomId, dbRoomName: dbRoom?.room_name });
                const capacity = dbRoom?.capacity || 'N/A';
                const statusLabel = state === 'available' ? 'Available' : state === 'occupied' ? 'Occupied' : 'Unavailable';

                const sizing = this.computeLabelSizing(width, height, mapContext, roomName);
                const roomNameSize = sizing.roomNameSize;
                const detailSize = sizing.detailSize;
                const insetX = sizing.insetX;
                const insetY = sizing.insetY;
                const labelOffset = this.getLabelOffset(roomName, roomId, width, height, mapContext);

                const safeName = this.fitText(roomName, width - (insetX * 2), roomNameSize, 0.92);
                const safeCapacity = this.fitText(String(capacity), width - (insetX * 2), detailSize, 0.92);

                group.appendChild(this.makeForeignObject({
                    x: x + insetX + labelOffset.x,
                    y: y + insetY + labelOffset.y,
                    width: Math.max(16, width - (insetX * 2)),
                    height: Math.max(16, height - (insetY * 2)),
                    roomName: safeName,
                    capacity: safeCapacity,
                    statusLabel,
                    state,
                    colors,
                    roomNameSize,
                    detailSize,
                    rotateDeg: mapContext.isForge ? 180 : 0,
                }));
            });
        },

        getMapContext(group) {
            const svg = group.closest('svg');
            const docName = (svg?.getAttribute('sodipodi:docname') || '').toLowerCase();

            return {
                isRightWing: /right wing/.test(docName),
                isLeftWing: /left wing/.test(docName),
                isCentralPart: /central part/.test(docName),
                isForge: /forge/.test(docName),
            };
        },

        getLabelFrame(group, rect) {
            const hasTransform = !!rect.getAttribute('transform');
            if (hasTransform && typeof group.getBBox === 'function') {
                try {
                    const bbox = group.getBBox();
                    return {
                        x: Number.isFinite(bbox.x) ? bbox.x : parseFloat(rect.getAttribute('x') || '0'),
                        y: Number.isFinite(bbox.y) ? bbox.y : parseFloat(rect.getAttribute('y') || '0'),
                        width: Number.isFinite(bbox.width) ? bbox.width : parseFloat(rect.getAttribute('width') || '0'),
                        height: Number.isFinite(bbox.height) ? bbox.height : parseFloat(rect.getAttribute('height') || '0'),
                    };
                } catch (e) {
                    // Fall back to rect attributes when bbox is unavailable.
                }
            }

            return {
                x: parseFloat(rect.getAttribute('x') || '0'),
                y: parseFloat(rect.getAttribute('y') || '0'),
                width: parseFloat(rect.getAttribute('width') || '0'),
                height: parseFloat(rect.getAttribute('height') || '0'),
            };
        },

        resolveRoomName({ group, dispatchRoomName, roomId, dbRoomName }) {
            const fallback = dbRoomName || dispatchRoomName;
            const svg = group.closest('svg');
            const docName = svg?.getAttribute('sodipodi:docname') || '';
            const isLeftWing = /left wing/i.test(docName);

            if (!isLeftWing) {
                return fallback;
            }

            // Left Wing strict override chain anchored around LT 1:
            // directly above LT 1 => LT 3, above that => LT 5.
            const fixedByRoomId = {
                '43': 'LT 1',
                '45': 'LT 3',
                '47': 'LT 5',
            };
            if (roomId && fixedByRoomId[roomId]) {
                return fixedByRoomId[roomId];
            }

            const fallbackByDispatch = {
                LT1: 'LT 1',
                LT3: 'LT 3',
                LT5: 'LT 5',
            };
            if (fallbackByDispatch[dispatchRoomName]) {
                return fallbackByDispatch[dispatchRoomName];
            }

            if (/^LT\s*3$/i.test(fallback)) {
                return 'LT 3';
            }

            if (/^LT\s*5$/i.test(fallback)) {
                return 'LT 5';
            }

            return fallback;
        },

        computeLabelSizing(width, height, mapContext = { isRightWing: false, isLeftWing: false, isCentralPart: false, isForge: false }, roomName = '') {
            const minSide = Math.max(16, Math.min(width, height));
            let roomNameSize = Math.max(12, Math.min(15, Math.round(minSide * 0.19)));
            let detailSize = Math.max(8, Math.min(10, Math.round(minSide * 0.13)));
            const insetX = Math.max(2, Math.min(8, Math.round(width * 0.06)));
            const insetY = Math.max(2, Math.min(8, Math.round(height * 0.07)));

            if (mapContext.isLeftWing && /^LT\s*[1-6]$/i.test(String(roomName))) {
                roomNameSize = 25;
                detailSize = 20;
            }

            if (mapContext.isRightWing) {
                roomNameSize = 25;
                detailSize = 20;
            }

            if (mapContext.isCentralPart) {
                roomNameSize = roomNameSize * 0.4;
                detailSize = detailSize * 0.4;
            }

            else{
                roomNameSize =30;
                detailSize = 20;
            }

            return { roomNameSize, detailSize, insetX, insetY };
        },

        getLabelOffset(roomName, roomId, width, height, mapContext = { isRightWing: false, isLeftWing: false, isCentralPart: false, isForge: false }) {
            const normalizedName = String(roomName || '').replace(/\s+/g, ' ').trim().toUpperCase();
            const isMsb11 = normalizedName === 'MSB 11';

            if (isMsb11) {
                return {
                    x: -Math.max(4, Math.round(width * 0.08)),
                    y: 0,
                };
            }

            return { x: 0, y: 0 };
        },

        normalizeState(rawStatus) {
            if (!rawStatus) {
                return 'unavailable';
            }

            if (rawStatus === 'base_booking' || rawStatus === 'at_capacity' || rawStatus === 'booked') {
                return 'occupied';
            }
            if (rawStatus === 'available') {
                return 'available';
            }
            return 'unavailable';
        },

        normalizeRoomKey(value) {
            return String(value || '')
                .trim()
                .toLowerCase()
                .replace(/[^a-z0-9]/g, '');
        },

        resolveRoomStatus({ statusKey, dispatchRoomName, roomId, dbRoomName }) {
            const directKeyCandidates = [statusKey, dispatchRoomName, dbRoomName, roomId]
                .filter(Boolean)
                .map((value) => String(value));

            for (const key of directKeyCandidates) {
                if (Object.prototype.hasOwnProperty.call(this.statuses, key)) {
                    return this.statuses[key];
                }
            }

            const normalizedCandidates = new Set(
                directKeyCandidates
                    .map((value) => this.normalizeRoomKey(value))
                    .filter(Boolean)
            );

            if (normalizedCandidates.size === 0) {
                return 'available';
            }

            for (const [key, status] of Object.entries(this.statuses || {})) {
                if (normalizedCandidates.has(this.normalizeRoomKey(key))) {
                    return status;
                }
            }

            // Backend only publishes occupied/unavailable rooms; missing entries are available.
            return 'available';
        },

        stateColors(state) {
            if (state === 'occupied') {
                return { primary: '#9a3412', secondary: '#c2410c' };
            }
            if (state === 'available') {
                return { primary: '#14532d', secondary: '#166534' };
            }
            return { primary: '#881337', secondary: '#9f1239' };
        },

        stateSurface(state) {
            if (state === 'occupied') {
                return { fill: '#ff5560cc', stroke: '#fdba74' };
            }
            if (state === 'available') {
                return { fill: '#76ff9f', stroke: '#a7f3d0' };
            }
            return { fill: '#fff1f2', stroke: '#fecdd3' };
        },

        stateHoverSurface(state) {
            if (state === 'occupied') {
                return { fill: '#f73643cc', stroke: '#fb923c' };
            }
            if (state === 'available') {
                return { fill: 'rgb(45, 247, 116)', stroke: '#86efac' };
            }
            return { fill: '#ffe4e6', stroke: '#fda4af' };
        },

        fitText(text, width, fontSize, coverage = 0.85) {
            const targetWidth = Math.max(30, width * coverage);
            const maxChars = Math.max(4, Math.floor(targetWidth / (fontSize * 0.62)));
            if (text.length <= maxChars) {
                return text;
            }
            return `${text.slice(0, Math.max(1, maxChars - 1))}…`;
        },

        makeForeignObject({ x, y, width, height, roomName, capacity, statusLabel, state, colors, roomNameSize, detailSize, rotateDeg = 0 }) {
            const fo = document.createElementNS('http://www.w3.org/2000/svg', 'foreignObject');
            fo.setAttribute('x', String(x));
            fo.setAttribute('y', String(y));
            fo.setAttribute('width', String(Math.max(24, width)));
            fo.setAttribute('height', String(Math.max(24, height)));
            fo.setAttribute('pointer-events', 'none');
            fo.setAttribute('class', 'room-overlay-label');

            const xhtmlNs = 'http://www.w3.org/1999/xhtml';
            const wrapper = document.createElementNS(xhtmlNs, 'div');
            wrapper.setAttribute('class', 'w-full h-full flex flex-col justify-center items-center p-1 text-center font-sans select-none overflow-hidden');
            wrapper.style.width = '100%';
            wrapper.style.height = '100%';
            wrapper.style.display = 'flex';
            wrapper.style.flexDirection = 'column';
            wrapper.style.justifyContent = 'center';
            wrapper.style.alignItems = 'center';
            wrapper.style.padding = '1px';
            wrapper.style.textAlign = 'center';
            wrapper.style.overflow = 'hidden';
            wrapper.style.boxSizing = 'border-box';

            const resetBlock = document.createElementNS(xhtmlNs, 'div');
            resetBlock.setAttribute('class', 'flex flex-col items-center justify-center text-center');
            resetBlock.style.width = '100%';
            resetBlock.style.height = '100%';
            resetBlock.style.display = 'flex';
            resetBlock.style.flexDirection = 'column';
            resetBlock.style.justifyContent = 'center';
            resetBlock.style.alignItems = 'center';
            resetBlock.style.transform = `rotate(${rotateDeg}deg)`;
            resetBlock.style.writingMode = 'horizontal-tb';
            resetBlock.style.direction = 'ltr';
            resetBlock.style.overflow = 'hidden';
            resetBlock.style.gap = '1px';

            const roomNameEl = document.createElementNS(xhtmlNs, 'span');
            roomNameEl.setAttribute(
                'class',
                `text-[11px] font-mono font-bold tracking-tight text-[#1d2d54] uppercase block truncate ${state === 'occupied' ? 'line-through' : ''}`.trim()
            );
            roomNameEl.style.fontSize = `${roomNameSize}px`;
            roomNameEl.style.lineHeight = '1.1';
            roomNameEl.style.maxWidth = '100%';
            roomNameEl.style.whiteSpace = 'nowrap';
            roomNameEl.style.overflow = 'hidden';
            roomNameEl.style.textOverflow = 'ellipsis';
            roomNameEl.style.color = state === 'occupied' ? '#9a3412' : state === 'unavailable' ? '#881337' : '#1d2d54';
            roomNameEl.textContent = roomName;

            const capacityEl = document.createElementNS(xhtmlNs, 'span');
            capacityEl.setAttribute('class', 'text-[9px] font-sans font-medium text-gray-500 block mt-0.5');
            capacityEl.style.fontSize = `${detailSize}px`;
            capacityEl.style.lineHeight = '1.1';
            capacityEl.style.maxWidth = '100%';
            capacityEl.style.whiteSpace = 'nowrap';
            capacityEl.style.overflow = 'hidden';
            capacityEl.style.textOverflow = 'ellipsis';
            capacityEl.style.color = colors.secondary;
            capacityEl.textContent = `Cap: ${capacity}`;

            const statusEl = document.createElementNS(xhtmlNs, 'span');
            statusEl.setAttribute('class', 'text-[9px] font-sans font-medium text-gray-500 block mt-0.5');
            statusEl.style.fontSize = `${detailSize}px`;
            statusEl.style.lineHeight = '1.1';
            statusEl.style.maxWidth = '100%';
            statusEl.style.whiteSpace = 'nowrap';
            statusEl.style.overflow = 'hidden';
            statusEl.style.textOverflow = 'ellipsis';
            statusEl.style.color = colors.secondary;
            statusEl.textContent = statusLabel;

            resetBlock.appendChild(roomNameEl);
            resetBlock.appendChild(capacityEl);
            resetBlock.appendChild(statusEl);
            wrapper.appendChild(resetBlock);
            fo.appendChild(wrapper);
            return fo;
        },
    };
}
</script>
@endonce

 