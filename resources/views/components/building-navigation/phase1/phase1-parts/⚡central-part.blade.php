<?php

use Livewire\Component;
use Livewire\Attributes\On;

new class extends Component{
    public array $roomStatuses = [];

    public function render(){
        return view('components.building-navigation.phase1.phase1-parts.⚡central-part');
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
        sodipodi:docname="Central Building - Central Part.svg"
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
            inkscape:zoom="0.1157493"
            inkscape:cx="2820.7513"
            inkscape:cy="1015.1249"
            inkscape:window-width="1920"
            inkscape:window-height="974"
            inkscape:window-x="-11"
            inkscape:window-y="-11"
            inkscape:window-maximized="1"
            inkscape:current-layer="svg1" />
        <defs
            id="defs1" />
        <g
            id="g2"
            class="room-banned"
            transform="matrix(7.6452939,0,0,8.7106617,-10.337322,-591.23841)">
            <rect
            style="fill:#fcfcfc;stroke:#000000;stroke-width:3.8624"
            id="rect1"
            width="515.77808"
            height="92.673973"
            x="170.02568"
            y="576.31512"
            transform="matrix(0.38210006,0,0,0.37864838,-58.81079,-72.430214)" />
        </g>
        <g
            id="g1"
            transform="matrix(7.5910194,0,0,8.711936,-0.07641341,-596.72723)">
            <rect
            class="building-section"
        @click="Livewire.dispatch('partSelected',{partName:'CentralPartFirstFloor'})"
            style="fill:#fcfcfc;stroke:#000000;stroke-width:3.87398"
            id="rect1-5"
            width="518.93903"
            height="92.662338"
            x="166.91927"
            y="485.54773"
            transform="matrix(0.38210006,0,0,0.37864838,-58.81079,-72.430214)" />
        </g>
        <g
            inkscape:label="Layer 1"
            inkscape:groupmode="layer"
            id="layer1" />
        </svg>
    </div>
</div>
