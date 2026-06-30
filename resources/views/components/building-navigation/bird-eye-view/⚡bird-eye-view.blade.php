<?php

use Livewire\Component;

new class extends Component{
   public function render(){
      return view('components.building-navigation.bird-eye-view.⚡bird-eye-view');
   }

   public function selectPhase(string $phaseName): void
   {
      $this->dispatch('phaseSelected', phaseName: $phaseName);
   }
}
?>

<div x-data>
   <svg
      width="1920"
      height="1080"
      viewBox="0 0 1920 1080"
      version="1.1"
      id="svg1"
      inkscape:version="1.4.2 (f4327f4, 2025-05-13)"
      sodipodi:docname="Phases.svg"
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
      inkscape:document-units="px"
      inkscape:zoom="0.68055556"
      inkscape:cx="960.2449"
      inkscape:cy="538.53061"
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
      <g
         id="g5" class="phase"
         wire:click="selectPhase('Phase1')"
         onclick="Livewire.dispatch('phaseSelected',{phaseName:'Phase1'})"
         @click="Livewire.dispatch('phaseSelected',{phaseName:'Phase1'})">
         <rect
            style="fill:#fcfcfc;stroke:#000000;stroke-width:5.39882"
            id="rect3"
            wire:click="selectPhase('Phase1')"
            onclick="Livewire.dispatch('phaseSelected',{phaseName:'Phase1'})"
            @click="Livewire.dispatch('phaseSelected',{phaseName:'Phase1'})"
            width="719.04675"
            height="749.90393"
            x="117.29296"
            y="102.59908" />
         <g
            id="g4">
         <rect
            style="fill:#fcfcfc;stroke:#000000;stroke-width:4.33411"
            id="rect1"
            width="449.74414"
            height="133.82576"
            x="201.98514"
            y="482.63818" />
         <rect
            style="fill:#fcfcfc;stroke:#000000;stroke-width:6.97274"
            id="rect2"
            width="134.12589"
            height="273.71774"
            x="204.77379"
            y="207.71255" />
         <rect
            style="fill:#fcfcfc;stroke:#000000;stroke-width:6.88202"
            id="rect2-8"
            width="134.21661"
            height="266.46152"
            x="515.97162"
            y="213.05325" />
         </g>
      </g>
      <g
         id="g21" class="phase"
         wire:click="selectPhase('Phase2')"
         onclick="Livewire.dispatch('phaseSelected',{phaseName:'Phase2'})"
         @click="Livewire.dispatch('phaseSelected',{phaseName:'Phase2'})">
         <rect
            style="fill:#fcfcfc;stroke:#000000;stroke-width:5.91496"
            id="rect5"
            wire:click="selectPhase('Phase2')"
            onclick="Livewire.dispatch('phaseSelected',{phaseName:'Phase2'})"
            @click="Livewire.dispatch('phaseSelected',{phaseName:'Phase2'})"
            width="956.57141"
            height="1015.3469"
            x="865.46942"
            y="10.285716" />
         <g
            id="g19">
         <rect
            style="fill:#fcfcfc;stroke:#000000;stroke-width:3.4489"
            id="rect6"
            width="416.8334"
            height="152.3436"
            x="990.6037"
            y="848.07306" />
         </g>
         <g
            id="g18">
         <rect
            style="fill:#fcfcfc;stroke:#000000;stroke-width:3.93309"
            id="rect7"
            width="248.83904"
            height="200.34921"
            x="995.25403"
            y="620.56012" />
         </g>
         <g
            id="g17">
         <rect
            style="fill:#fcfcfc;stroke:#000000;stroke-width:4.36356"
            id="rect8"
            width="223.42894"
            height="174.93916"
            x="1314.3264"
            y="620.77533" />
         </g>
         <g
            id="g15">
         <rect
            style="fill:#fcfcfc;stroke:#000000;stroke-width:5.30342"
            id="rect9"
            width="196.04012"
            height="181.34624"
            x="994.46973"
            y="393.49017" />
         </g>
         <g
            id="g16">
         <rect
            style="fill:#fcfcfc;stroke:#000000;stroke-width:4.64797"
            id="rect10"
            width="215.79761"
            height="112.94045"
            x="1214.5502"
            y="468.1012" />
         </g>
         <g
            id="g12"
            transform="matrix(1.0078793,-0.03082577,0.01767004,0.57773972,-60.641139,302.91573)">
         <path
            style="fill:#fcfcfc;stroke:#000000;stroke-width:5.91496"
            id="path11"
            sodipodi:type="arc"
            sodipodi:cx="1645.7142"
            sodipodi:cy="387.91837"
            sodipodi:rx="82.285713"
            sodipodi:ry="116.08163"
            sodipodi:start="1.6712227"
            sodipodi:end="4.6892666"
            sodipodi:open="true"
            sodipodi:arc-type="arc"
            d="M 1637.4645,503.41512 A 82.285713,116.08163 0 0 1 1563.49,383.4327 82.285713,116.08163 0 0 1 1643.8118,271.86777" />
         <path
            style="fill:#fcfcfc;stroke:#000000;stroke-width:5.91496"
            d="m 1639.8367,506.93878 5.8776,-236.57143"
            id="path12" />
         </g>
         <g
            id="g20">
         <rect
            style="fill:#fcfcfc;stroke:#000000;stroke-width:5.91496"
            id="rect12"
            width="99.918365"
            height="138.12245"
            x="1636.8979"
            y="208.65306" />
         </g>
         <g
            id="g14">
         <rect
            style="fill:#fcfcfc;stroke:#000000;stroke-width:4.97568"
            id="rect13"
            width="92.041321"
            height="100.85765"
            x="1344.0201"
            y="212.59158" />
         <rect
            style="fill:#fcfcfc;stroke:#000000;stroke-width:5.91496"
            id="rect14"
            width="94.040817"
            height="85.224487"
            x="1344.4897"
            y="121.95918" />
         <rect
            style="fill:#fcfcfc;stroke:#000000;stroke-width:5.91496"
            id="rect14-2"
            width="94.040817"
            height="85.224487"
            x="1344.386"
            y="34.079929" />
         </g>
      </g>
   </g>
   </svg>
</div>