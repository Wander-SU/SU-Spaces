<?php

use Livewire\Component;
use Livewire\Attributes\On;

new class extends Component{
    public array $roomStatuses = [];

    public function render(){
        return view('components.building-navigation.phase1.phase1-parts.⚡left-wing');
    }

    #[On('statusUpdated')]
    public function statusUpdated(array $statuses){
        $this->roomStatuses = $statuses;
    }


    public function roomColor(string $roomName): string
    {
        return match($this->roomStatuses[$roomName] ?? 'available'){
            'base_booking' => '#ef4444',
            'at_capacity' => '#f97316',
            default => '#22bf34ff'
        };
    }
}
?>

<div>
    <div x-data>
        <svg
        width="1920mm"
        height="1080mm"
        viewBox="0 0 1920 1080"
        version="1.1"
        id="svg1"
        inkscape:version="1.4.2 (f4327f4, 2025-05-13)"
        sodipodi:docname="Central Building - Left Wing.svg"
        xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape"
        xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd"
        xmlns="http://www.w3.org/2000/svg"
        xmlns:svg="http://www.w3.org/2000/svg">
        <sodipodi:namedview
            id="namedview1"
            pagecolor="#505050"
            bordercolor="#ffffff"
            borderopacity="1"
            inkscape:showpageshadow="0"
            inkscape:pageopacity="0"
            inkscape:pagecheckerboard="1"
            inkscape:deskcolor="#505050"
            inkscape:document-units="mm"
            inkscape:zoom="0.16369423"
            inkscape:cx="2837.6076"
            inkscape:cy="2095.3701"
            inkscape:window-width="1920"
            inkscape:window-height="974"
            inkscape:window-x="-11"
            inkscape:window-y="-11"
            inkscape:window-maximized="1"
            inkscape:current-layer="layer1" />
        <defs
            id="defs1" />
        <g
            inkscape:label="Layer 1"
            inkscape:groupmode="layer"
            id="layer1"
            >
            <rect
            style="fill:#fcfcfc;stroke:#000000;stroke-width:1.337"
            id="rect2"
            width="1349.2645"
            height="254.75757"
            x="42.993107"
            y="780.10559" />
            <g
            id="g8"
            class="room"
            @click="Livewire.dispatch('roomSelected',{roomName:'LT2',room_id:44})"
            >
            <rect
                style="fill:{{ $this->roomColor('LT 2') }};stroke:#000000;stroke-width:2.08475"
                id="rect5-78-59"
                width="511.89023"
                height="237.18636"
                x="872.34467"
                y="786.63116" />
            </g>
            <g
            id="g3"
            class="room"
            @click="Livewire.dispatch('roomSelected',{roomName:'LT1',room_id:43})">
            <rect
                style="fill:{{ $this->roomColor('LT 1') }};stroke:#000000;stroke-width:2.11517"
                id="rect5-78-2"
                width="516.69452"
                height="241.88896"
                x="50.243286"
                y="784.24762" />
            </g>
            <rect
            style="fill:#fcfcfc;stroke:#000000;stroke-width:1.33277"
            id="rect2-1"
            width="1349.2688"
            height="253.14548"
            x="45.2985"
            y="523.9646" />
            <g
            id="g7"
            class="room"
            @click="Livewire.dispatch('roomSelected',{roomName:'LT4',room_id:46})">
            <rect
                style="fill:{{ $this->roomColor('LT 4') }};stroke:#000000;stroke-width:2.08475"
                id="rect5-78-7"
                width="511.89023"
                height="237.18636"
                x="873.98993"
                y="531.18982" />
            </g>
            <rect
            style="fill:#fcfcfc;stroke:#000000;stroke-width:1.337"
            id="rect2-9"
            width="1349.2645"
            height="254.75757"
            x="44.283516"
            y="265.89331" />
            <g
            id="g6"
            class="room"
            @click="Livewire.dispatch('roomSelected',{roomName:'LT6',room_id:48})"
            >
            <rect
                style="fill:{{ $this->roomColor('LT 6') }};stroke:#000000;stroke-width:2.08475"
                id="rect5-78-6"
                width="511.89023"
                height="237.18636"
                x="873.98474"
                y="274.20636" />
            </g>
            <g
            id="g4"
            class="room"
            @click="Livewire.dispatch('roomSelected',{roomName:'LT3',room_id:45})">
            <rect
                style="fill:{{ $this->roomColor('LT 3') }};stroke:#000000;stroke-width:2.18035"
                id="rect5-78-5"
                width="560.39691"
                height="236.98273"
                x="889.96069"
                y="341.16168"
                transform="matrix(0.9134797,0,0,0.98089053,-759.34528,192.22763)" />
            </g>
            <g
            id="g5"
            class="room"
            @click="Livewire.dispatch('roomSelected',{roomName:'LT5',room_id:47})">
            <rect
                style="fill:{{ $this->roomColor('LT 5') }};stroke:#000000;stroke-width:2.20239"
                id="rect5-78"
                width="560.37396"
                height="241.80716"
                x="888.21173"
                y="81.121422"
                transform="matrix(0.9134797,0,0,0.98089053,-759.34528,192.22763)" />
            </g>
            <g
            id="g9"
            class="room"
            @click="Livewire.dispatch('roomSelected',{roomName:'Kitchen5',room_id:67})">
            <rect
                style="fill:{{ $this->roomColor('Kitchen 5') }};stroke:#000000;stroke-width:2.0351"
                id="rect5-78-26"
                width="487.69498"
                height="237.23601"
                x="1405.5758"
                y="792.91718" />
            </g>
        </g>
        </svg>
    </div>
</div>
