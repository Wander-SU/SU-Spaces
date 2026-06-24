<?php

use Livewire\Component;
use Livewire\Attributes\On;

new class extends Component{
    public array $roomStatuses = [];

    public function render(){
        return view('components.building-navigation.phase2.phase2-buildings.⚡ovb-view');
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
        sodipodi:docname="OVB.svg"
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
            inkscape:zoom="0.090031829"
            inkscape:cx="2349.1692"
            inkscape:cy="2249.2046"
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
            style="fill:#fcfcfc;stroke:#000000;stroke-width:3.32574"
            id="rect1"
            width="1455.6846"
            height="248.63487"
            x="24.629547"
            y="773.7879" />
            <rect
            style="fill:#fcfcfc;stroke:#000000;stroke-width:3.32574"
            id="rect1-5"
            width="1455.6846"
            height="248.63487"
            x="24.602396"
            y="523.48718" />
            <g
            id="g4"
            class="room"
        @click="Livewire.dispatch('roomSelected',{roomName:'ovbShaba',room_id:70})"
            transform="matrix(1.7120264,0,0,1.7264906,61.665111,-321.84934)">
            <rect
                style="fill:{{ $this->roomColor('SLS Shaba') }};stroke:#000000;stroke-width:1.565"
                id="rect2"
                width="337.67953"
                height="126.75971"
                x="34.28746"
                y="640.03265" />
            </g>
            <g
            id="g5"
            class="room"
        @click="Livewire.dispatch('roomSelected',{roomName:'ovbZumaridi',room_id:71})"
            transform="matrix(1.3949108,0,0,1.8018812,661.76012,-370.51297)">
            <rect
                style="fill:{{ $this->roomColor('SLS Zumaridi') }};stroke:#000000;stroke-width:1.49535"
                id="rect3"
                width="433.3385"
                height="122.34736"
                x="32.177677"
                y="641.72681" />
            </g>
        </g>
        </svg>
    </div>
</div>