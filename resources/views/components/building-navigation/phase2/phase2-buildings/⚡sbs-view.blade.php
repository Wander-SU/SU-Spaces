<?php

use Livewire\Component;
use Livewire\Attributes\On;

new class extends Component{
    public array $roomStatuses = [];

    public function render(){
        return view('components.building-navigation.phase2.phase2-buildings.⚡sbs-view');
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
        sodipodi:docname="SBS - View.svg"
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
            inkscape:cx="3820.9537"
            inkscape:cy="2100.9355"
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
            style="fill:#fcfcfc;stroke:#000000;stroke-width:1.81565"
            id="rect1"
            width="1489.6953"
            height="999.28076"
            x="31.295748"
            y="56.232082" />
            <g
            id="g2-0-4"
            class="room"
        @click="Livewire.dispatch('roomSelected',{roomName:'sbs2'})"
            transform="matrix(0.61616923,0,0,0.59950684,-277.37749,439.76232)">
            <rect
                style="fill:{{ $this->roomColor('SBS 2') }};stroke:#000000;stroke-width:1.565"
                id="rect1-4-9-9"
                width="480.04895"
                height="295.78775"
                x="532.4856"
                y="700.57141" />
            </g>
            <g
            id="g2-0-4-5"
            class="room"
        @click="Livewire.dispatch('roomSelected',{roomName:'sbs1'})"
            transform="matrix(0.76750932,0,0,0.59913492,-146.40309,443.62328)">
            <rect
                style="fill:{{ $this->roomColor('SBS 1') }};stroke:#000000;stroke-width:1.565"
                id="rect1-4-9-9-2"
                width="480.04895"
                height="295.78775"
                x="659.75531"
                y="698.83942" />
            </g>
        </g>
        </svg>
    </div>
</div>