<?php

use Livewire\Component;

new class extends Component{
   public function render(){
      return view('components.building-navigation.phase2.phase2-buildings.⚡unilib-view');
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
        sodipodi:docname="UniLib - View.svg"
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
            inkscape:cx="1593.8808"
            inkscape:cy="2304.7405"
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
            style="fill:#fcfcfc;stroke:#000000;stroke-width:3.08848"
            id="rect1"
            width="1308.9969"
            height="238.45305"
            x="36.085888"
            y="794.35925" />
            <rect
            style="fill:#fcfcfc;stroke:#000000;stroke-width:3.08848"
            id="rect1-5"
            width="1308.9969"
            height="238.45305"
            x="36.061474"
            y="554.30853" />
            <rect
            style="fill:#fcfcfc;stroke:#000000;stroke-width:3.08848"
            id="rect1-2"
            width="1308.9969"
            height="238.45305"
            x="35.961544"
            y="315.84866" />
            <rect
            style="fill:#fcfcfc;stroke:#000000;stroke-width:3.08848"
            id="rect1-6"
            width="1308.9969"
            height="238.45305"
            x="36.060726"
            y="74.823952" />
            <g
            id="g4"
            class="room"
        @click="Livewire.dispatch('roomSelected',{roomName:'basementClassroom'})"
            transform="matrix(2.0468653,0,0,1.6526911,-21.776869,-254.23138)">
            <rect
                style="fill:#fcfcfc;stroke:#000000;stroke-width:1.565"
                id="rect2"
                width="337.67953"
                height="126.75971"
                x="34.28746"
                y="640.03265" />
            </g>
            <g
            id="g5"
            class="room"
        @click="Livewire.dispatch('roomSelected',{roomName:'librarySeminar'})"
            transform="matrix(1.010801,0,0,1.6612919,8.9596941,-733.85118)">
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