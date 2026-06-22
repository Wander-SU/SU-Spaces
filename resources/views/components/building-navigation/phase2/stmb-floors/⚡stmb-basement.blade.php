<?php

use Livewire\Component;

new class extends Component{
   public function render(){
      return view('components.building-navigation.phase2.stmb-floors.⚡stmb-basement');
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
        sodipodi:docname="STMB - Basement.svg"
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
            inkscape:cx="4076.2075"
            inkscape:cy="2446.5099"
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
            style="fill:#fcfcfc;stroke:#000000;stroke-width:1.83218"
            id="rect1"
            width="1541.6296"
            height="1036.6688"
            x="29.225998"
            y="25.069925" />
            <g
            id="g3"
            class="room"
            @click="Livewire.dispatch('roomSelected',{roomName:'b1-03'})"
            transform="matrix(0.50034878,0,0,1.1806045,158.64227,-16.749839)">
            <rect
                style="fill:#fcfcfc;stroke:#000000;stroke-width:1.565"
                id="rect2"
                width="1072.2625"
                height="324.17239"
                x="401.05942"
                y="45.716618" />
            </g>
            <g
            id="g7"
            class="room"
            @click="Livewire.dispatch('roomSelected',{roomName:'b1-04'})">
            <rect
                style="fill:#fcfcfc;stroke:#000000;stroke-width:1.07625"
                id="rect3"
                width="524.15179"
                height="399.47006"
                x="375.87869"
                y="646.02234"
                ry="0" />
            </g>
            <g
            id="g5"
            class="room"
            @click="Livewire.dispatch('roomSelected',{roomName:'b1-01'})">
            <rect
                style="fill:#fcfcfc;stroke:#000000;stroke-width:1.5096"
                id="rect4"
                width="581.90326"
                height="376.17847"
                x="910.14856"
                y="39.454834" />
            </g>
            <g
            id="g6"
            class="room"
            @click="Livewire.dispatch('roomSelected',{roomName:'b1-06'})">
            <rect
                style="fill:#fcfcfc;stroke:#000000;stroke-width:1.49725"
                id="rect5"
                width="581.91565"
                height="399.04913"
                x="914.29846"
                y="646.23285" />
            </g>
        </g>
        </svg>
    </div>
</div>