<?php

use Livewire\Component;
use Livewire\Attributes\On;

new class extends Component{
    public array $roomStatuses = [];
        
    public function render(){
        return view('components.building-navigation.phase2.phase2-buildings.⚡stc-view');
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

<div x-data>
    <svg
    width="1920mm"
    height="1080mm"
    viewBox="0 0 1920 1080"
    version="1.1"
    id="svg1"
    inkscape:version="1.4.2 (f4327f4, 2025-05-13)"
    sodipodi:docname="STC - View.svg"
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
        inkscape:zoom="0.12732423"
        inkscape:cx="3773.8299"
        inkscape:cy="2909.8938"
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
        id="layer1">
        <rect
        style="fill:#fcfcfc;stroke:#000000;stroke-width:2.38336"
        id="rect1"
        width="1295.0095"
        height="143.53471"
        x="26.836338"
        y="919.89282" />
        <rect
        style="fill:#fcfcfc;stroke:#000000;stroke-width:2.38336"
        id="rect1-5"
        width="1295.0095"
        height="143.53471"
        x="26.812181"
        y="775.39642" />
        <rect
        style="fill:#fcfcfc;stroke:#000000;stroke-width:2.38336"
        id="rect1-2"
        width="1295.0095"
        height="143.53471"
        x="26.713322"
        y="631.8576" />
        <rect
        style="fill:#fcfcfc;stroke:#000000;stroke-width:2.38336"
        id="rect1-6"
        width="1295.0095"
        height="143.53471"
        x="26.81144"
        y="486.77487" />
        <rect
        style="fill:#fcfcfc;stroke:#000000;stroke-width:2.38336"
        id="rect1-59"
        width="1295.0095"
        height="143.53471"
        x="26.73468"
        y="343.69745" />
        <rect
        style="fill:#fcfcfc;stroke:#000000;stroke-width:2.38336"
        id="rect1-5-9"
        width="1295.0095"
        height="143.53471"
        x="26.710558"
        y="199.20107" />
        <g
        id="g4"
          class="room"
       @click="Livewire.dispatch('roomSelected',{roomName:'chelaLab',room_id:38})"
        >
        <rect
            style="fill:{{ $this->roomColor('Chela Lab') }};stroke:#000000;stroke-width:1.565"
            id="rect2"
            width="337.67953"
            height="126.75971"
            x="34.28746"
            y="640.03265" />
        </g>
        <g
        id="g5"
          class="room"
       @click="Livewire.dispatch('roomSelected',{roomName:'stcSeminar',room_id:39})"
        >
        <rect
            style="fill:{{ $this->roomColor('Seminar Room (STC)') }};stroke:#000000;stroke-width:1.565"
            id="rect3"
            width="433.26886"
            height="134.03281"
            x="383.39618"
            y="490.41464" />
        </g>
        <g
        id="g6"
          class="room"
       @click="Livewire.dispatch('roomSelected',{roomName:'iLabKifaru',room_id:40})"
        >
        <rect
            style="fill:{{ $this->roomColor('iLab Kifaru') }};stroke:#000000;stroke-width:1.565"
            id="rect4"
            width="345.99167"
            height="123.64267"
            x="38.44352"
            y="353.26477" />
        </g>
    </g>
    </svg>
</div>