<?php

use Livewire\Component;
use Livewire\Attributes\On;

new class extends Component{
    public array $roomStatuses = [];

    public function render(){
        return view('components.building-navigation.phase1.phase1-parts.⚡right-wing');
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
        sodipodi:docname="Central Building - Right Wing.svg"
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
            inkscape:cx="2752.0823"
            inkscape:cy="1799.086"
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
            style="fill:#fcfcfc;stroke:#000000;stroke-width:1.30472"
            id="rect1"
            width="1347.9626"
            height="232.46373"
            x="41.010098"
            y="810.00122" />
            <rect
            style="fill:#fcfcfc;stroke:#000000;stroke-width:1.337"
            id="rect2"
            width="1349.2645"
            height="254.75757"
            x="41.02877"
            y="554.27087" />
            <g
            id="g9"
            class="room"
            @click="Livewire.dispatch('roomSelected',{roomName:'room4',room_id:52})"
            >
            <rect
                style="fill:{{ $this->roomColor('RM 4') }};stroke:#000000;stroke-width:1.24905"
                id="rect5-8"
                width="203.9731"
                height="213.67099"
                x="689.17224"
                y="572.11835" />
            </g>
            <g
            id="g10"
            class="room"
            @click="Livewire.dispatch('roomSelected',{roomName:'room5',room_id:53})"
            >
            <rect
                style="fill:{{ $this->roomColor('RM 5') }};stroke:#000000;stroke-width:1.18336"
                id="rect5-3"
                width="183.02655"
                height="213.73668"
                x="493.54547"
                y="571.8714" />
            </g>
            <g
            id="g11"
            class="room"
            @click="Livewire.dispatch('roomSelected',{roomName:'room6',room_id:54})"
            >>
            <rect
                style="fill:{{ $this->roomColor('RM 6') }};stroke:#000000;stroke-width:1.69687"
                id="rect6-4"
                width="376.47217"
                height="211.6069"
                x="104.43126"
                y="573.11334" />
            </g>
            <rect
            style="fill:#fcfcfc;stroke:#000000;stroke-width:1.42492"
            id="rect3"
            width="1349.7175"
            height="249.50818"
            x="42.418854"
            y="303.03061" />
            <g
            id="g12"
            class="room"
            @click="Livewire.dispatch('roomSelected',{roomName:'room7',room_id:55})"
            >>
            <rect
                style="fill:{{ $this->roomColor('RM 7') }};stroke:#000000;stroke-width:1.23917"
                id="rect5-61"
                width="200.75034"
                height="213.68088"
                x="1146.6587"
                y="321.5643" />
            </g>
            <g
            id="g13"
            class="room-banned"
            @click="Livewire.dispatch('roomSelected',{roomName:'AppleLab'})">>
            <rect
                style="fill:#fcfcfc;stroke:#000000;stroke-width:1.36197"
                id="rect5-65"
                width="242.65201"
                height="213.55807"
                x="894.59595"
                y="320.00937" />
            </g>
            <g
            id="g14"
            class="room"
            @click="Livewire.dispatch('roomSelected',{roomName:'room8',room_id:56})"
            >>
            <rect
                style="fill:{{ $this->roomColor('RM 8') }};stroke:#000000;stroke-width:1.09147"
                id="rect5-6"
                width="155.64088"
                height="213.82857"
                x="729.65918"
                y="321.42764" />
            </g>
            <g
            id="g15"
            class="room"
            @click="Livewire.dispatch('roomSelected',{roomName:'room9',room_id:57})"
            >>
            <rect
                style="fill:{{ $this->roomColor('RM 9') }};stroke:#000000;stroke-width:1.56732"
                id="rect5-86"
                width="321.64667"
                height="213.35272"
                x="398.36905"
                y="320.16455" />
            </g>
            <g
            id="g16"
            class="room"
            @click="Livewire.dispatch('roomSelected',{roomName:'room10',room_id:58})"
            >>
            <rect
                style="fill:{{ $this->roomColor('RM 10') }};stroke:#000000;stroke-width:1.47453"
                id="rect5-7"
                width="284.56396"
                height="213.44551"
                x="102.48858"
                y="320.00803" />
            </g>
            <rect
            style="fill:#fcfcfc;stroke:#000000;stroke-width:1.42492"
            id="rect3-9"
            width="1349.7175"
            height="249.50818"
            x="42.849148"
            y="51.316788" />
            <g
            id="g20"
            class="room"
            @click="Livewire.dispatch('roomSelected',{roomName:'languageLab',room_id:65})"
            >>
            <rect
                style="fill:{{ $this->roomColor('Language Lab') }};stroke:#000000;stroke-width:1.17816"
                id="rect5-35"
                width="181.41544"
                height="213.7419"
                x="753.78784"
                y="71.025871" />
            </g>
            <g
            id="g17"
            class="room"
            @click="Livewire.dispatch('roomSelected',{roomName:'kindarumaLab',room_id:62})"
            transform="matrix(0.92205308,0,0,1.0003383,100.05284,-6.5270993)">
            <rect
                style="fill:{{ $this->roomColor('Kindaruma Lab') }};stroke:#000000;stroke-width:1.82064"
                id="rect5-78"
                width="434.53619"
                height="213.09941"
                x="910.81238"
                y="76.08593" />
            </g>
            <g
            id="g18"
            transform="matrix(0.73184894,0,0,1.0011302,89.376986,-3.4389426)"
            class="room"
            @click="Livewire.dispatch('roomSelected',{roomName:'suswaLab',room_id:59})"
            >
            <rect
                style="fill:{{ $this->roomColor('Suswa Lab') }};stroke:#000000;stroke-width:1.6733"
                id="rect5-618"
                width="366.79782"
                height="213.24675"
                x="532.66663"
                y="75.907249" />
            </g>
            <g
            id="g6"
            class="room"
            @click="Livewire.dispatch('roomSelected',{roomName:'room1',room_id:49})"
            >>
            <rect
                style="fill:{{ $this->roomColor('RM 1') }};stroke:#000000;stroke-width:1.61905"
                id="rect4"
                width="342.60718"
                height="213.30104"
                x="827.5863"
                y="817.88824" />
            </g>
            <g
            id="g7"
            class="room"
            @click="Livewire.dispatch('roomSelected',{roomName:'room2',room_id:50})"
            >
            <rect
                style="fill:{{ $this->roomColor('RM 2') }};stroke:#000000;stroke-width:1.56732"
                id="rect5"
                width="321.64667"
                height="213.35272"
                x="494.59708"
                y="817.86243" />
            </g>
            <g
            id="g8"
            class="room"
            @click="Livewire.dispatch('roomSelected',{roomName:'room3',room_id:51})"
            >
            <rect
                style="fill:{{ $this->roomColor('RM 3') }};stroke:#000000;stroke-width:1.69687"
                id="rect6"
                width="376.47217"
                height="211.6069"
                x="106.74348"
                y="817.92712" />
            </g>
            <g
            id="g19"
            class="room"
            @click="Livewire.dispatch('roomSelected',{roomName:'masingaLab',room_id:60})"
            transform="matrix(0.87328698,0,0,1.000548,13.174423,-0.09919053)">
            <rect
                style="fill:{{ $this->roomColor('Masinga Lab') }};stroke:#000000;stroke-width:1.7901"
                id="rect5-4"
                width="420.01981"
                height="213.12994"
                x="104.43142"
                y="74.437836" />
            </g>
        </g>
        </svg>
    </div>
</div>