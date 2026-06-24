<?php

use Livewire\Component;
use Livewire\Attributes\On;

new class extends Component{
    public array $roomStatuses =[];

    public function render(){
        return view('components.building-navigation.phase2.stmb-floors.⚡stmb-f5');
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
    sodipodi:docname="STMB - F5.svg"
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
        inkscape:zoom="0.063662117"
        inkscape:cx="1531.5231"
        inkscape:cy="1468.6913"
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
        style="fill:#fcfcfc;stroke:#000000;stroke-width:1.565"
        id="rect1"
        width="1472.3265"
        height="1025.6327"
        x="33.357491"
        y="24.90925" />
        <g
        id="g8"
          class="room"
       @click="Livewire.dispatch('roomSelected',{roomName:'f5-04',room_id:20})"
        transform="translate(12.78607,1.399037)">
        <rect
            style="fill:{{ $this->roomColor('STMB F5-04') }};stroke:#000000;stroke-width:1.93521"
            id="rect4-5"
            width="638.97113"
            height="357.91144"
            x="37.24678"
            y="665.42462" />
        </g>
        <g
        id="g5"
         class="room"
       @click="Livewire.dispatch('roomSelected',{roomName:'f5-01',room_id:17})"
        transform="translate(12.78607,1.399037)">
        <rect
            style="fill:{{ $this->roomColor('STMB F5-01') }};stroke:#000000;stroke-width:1.565"
            id="rect2"
            width="619.25238"
            height="328.32843"
            x="764.71429"
            y="39.482536" />
        </g>
        <g
        id="g9"
          class="room"
       @click="Livewire.dispatch('roomSelected',{roomName:'f5-05',room_id:21})"
        transform="translate(12.78607,1.399037)">
        <rect
            style="fill:{{ $this->roomColor('STMB F5-05') }};stroke:#000000;stroke-width:1.63522"
            id="rect3"
            width="719.1795"
            height="362.85004"
            x="677.22015"
            y="665.73883" />
        </g>
        <g
        id="g6"
          class="room"
       @click="Livewire.dispatch('roomSelected',{roomName:'f5-02',room_id:18})"
        transform="translate(12.78607,1.399037)">
        <rect
            style="fill:{{ $this->roomColor('STMB F5-02') }};stroke:#000000;stroke-width:1.92453"
            id="rect4"
            width="641.75116"
            height="336.28104"
            x="37.584267"
            y="37.58427" />
        </g>
        <g
        id="g7"
          class="room"
       @click="Livewire.dispatch('roomSelected',{roomName:'f5-03',room_id:19})"
        transform="translate(12.78607,1.399037)">
        <rect
            style="fill:{{ $this->roomColor('STMB F5-03') }};stroke:#000000;stroke-width:1.92453"
            id="rect4-7"
            width="638.0777"
            height="290.20795"
            x="38.214272"
            y="374.45343" />
        </g>
    </g>
    </svg>
</div>