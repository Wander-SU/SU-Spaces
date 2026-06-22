<?php

use Livewire\Component;

new class extends Component{
   public function render(){
      return view('components.building-navigation.phase2.phase2-buildings.⚡forge-view');
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
        sodipodi:docname="Forge.svg"
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
            inkscape:zoom="0.18006366"
            inkscape:cx="2699.0455"
            inkscape:cy="2690.7151"
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
            style="fill:#fcfcfc;stroke:#000000;stroke-width:2.12895"
            id="rect1"
            width="649.01794"
            height="228.52333"
            x="-1723.741"
            y="-822.84247"
            transform="scale(-1)" />
            <rect
            style="fill:#fcfcfc;stroke:#000000;stroke-width:2.12923"
            id="rect1-5"
            width="649.02063"
            height="228.58096"
            x="-1723.7557"
            y="-1052.8923"
            transform="scale(-1)" />
            <g
            id="g4"
            class="room"
        @click="Livewire.dispatch('roomSelected',{roomName:'chemistryLab'})"
            transform="matrix(-0.76330814,0,0,-1.5868385,1700.6544,1825.4467)">
            <rect
                style="fill:#fcfcfc;stroke:#000000;stroke-width:1.565"
                id="rect2"
                width="337.67953"
                height="126.75971"
                x="34.28746"
                y="640.03265" />
            </g>
            <g
            id="g4-1"
            class="room"
        @click="Livewire.dispatch('roomSelected',{roomName:'electronic&machineLab'})"
            transform="matrix(-0.76330814,0,0,-1.5868385,1699.9964,2058.1754)">
            <rect
                style="fill:#fcfcfc;stroke:#000000;stroke-width:1.565"
                id="rect2-6"
                width="337.67953"
                height="126.75971"
                x="34.28746"
                y="640.03265" />
            </g>
            <rect
            style="fill:#fcfcfc;stroke:#000000;stroke-width:2.46006"
            id="rect1-5-8"
            width="867.6286"
            height="228.25014"
            x="-1073.1017"
            y="-1049.5017"
            transform="scale(-1)" />
            <g
            id="g4-1-9"
            class="room"
        @click="Livewire.dispatch('roomSelected',{roomName:'forge1'})"
            transform="matrix(-1.2377131,0,0,-1.5369605,1078.1325,2011.7789)">
            <rect
                style="fill:#fcfcfc;stroke:#000000;stroke-width:1.565"
                id="rect2-6-8"
                width="337.67953"
                height="126.75971"
                x="34.28746"
                y="640.03265" />
            </g>
            <g
            id="g4-1-9-3"
            class="room"
        @click="Livewire.dispatch('roomSelected',{roomName:'forge2'})"
            transform="matrix(-1.0204974,0,0,-1.5385154,606.13966,2010.8906)">
            <rect
                style="fill:#fcfcfc;stroke:#000000;stroke-width:1.565"
                id="rect2-6-8-8"
                width="337.67953"
                height="126.75971"
                x="34.28746"
                y="640.03265" />
            </g>
            <rect
            style="fill:#fcfcfc;stroke:#000000;stroke-width:2.46109"
            id="rect1-5-9"
            width="868.36224"
            height="228.2491"
            x="-1075.9395"
            y="-822.30646"
            transform="scale(-1)" />
            <g
            id="g4-1-8"
            class="building-section-banned"
            transform="matrix(-2.2378547,0,0,-1.5772671,1093.2543,1816.6147)">
            <rect
                style="fill:#fcfcfc;stroke:#000000;stroke-width:1.565"
                id="rect2-6-80"
                width="337.67953"
                height="126.75971"
                x="34.28746"
                y="640.03265" />
            </g>
            <g
            id="g5"
            class="room"
        @click="Livewire.dispatch('roomSelected',{roomName:'physicsLab'})"
            transform="matrix(-0.62192195,0,0,-1.656131,1439.6758,1874.5834)">
            <rect
                style="fill:#fcfcfc;stroke:#000000;stroke-width:1.49535"
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