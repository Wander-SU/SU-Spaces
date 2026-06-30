<?php

use Livewire\Component;

new class extends Component{
   public function render(){
      return view('components.building-navigation.phase2.⚡phase2-view');
   }
}
?>

<style>
   .svg-scroll-container {
      background: #F5F6F8;
      border: 1px solid #D9D9D9 !important;
      border-radius: 16px;
      padding: 12px;
   }

   .svg-scroll-container > div[x-data] {
      min-width: 1100px;
   }

   #svg1 {
      display: block;
      width: 100%;
      height: auto;
      background: transparent;
   }

   #rect5 {
      fill: #F9FAFB !important;
      stroke: #D9D9D9 !important;
      stroke-width: 2.5 !important;
   }

   .campus-land {
      fill: #E9F7EE;
   }

   .campus-path {
      fill: #EBEEF2;
   }

   .campus-tree {
      fill: #BDE3C5;
   }

   .building {
      cursor: pointer;
   }

   .building rect,
   .building path {
      fill: #FFFFFF !important;
      stroke: #D9D9D9 !important;
      stroke-width: 2.4 !important;
      filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.08));
      transition: all .2s ease;
   }

   .building:hover rect,
   .building:hover path {
      stroke: #0D6EFD !important;
      filter: drop-shadow(0 8px 18px rgba(13, 110, 253, 0.18));
      translate: 0 -4px;
   }

   .building:active rect,
   .building:active path {
      stroke: #0D6EFD !important;
      filter: drop-shadow(0 0 0 2px rgba(13, 110, 253, 0.2)) drop-shadow(0 10px 20px rgba(13, 110, 253, 0.2));
   }

   .building-banned {
      cursor: not-allowed;
   }

   .building-banned rect,
   .building-banned path {
      fill: #FFFFFF !important;
      stroke: #BDBDBD !important;
      stroke-width: 2.4 !important;
      opacity: .45;
      transition: all .2s ease;
      filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.05));
   }

   .building-banned:hover rect,
   .building-banned:hover path {
      stroke: #BDBDBD !important;
      translate: 0 0;
      filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.05));
   }

   .building-label,
   .building-icon,
   .building-icon-mark,
   .lock-icon,
   .lock-body,
   .lock-shackle {
      pointer-events: none;
   }

   .building-label {
      fill: #3D4956;
      font-size: 20px;
      font-weight: 700;
      letter-spacing: .5px;
      text-anchor: middle;
   }

   .building-icon {
      fill: #EAF2FF;
      stroke: #0D6EFD;
      stroke-width: 2;
   }

   .building-icon-mark {
      fill: none;
      stroke: #0D6EFD;
      stroke-width: 2;
      stroke-linecap: round;
      stroke-linejoin: round;
   }

   .lock-icon {
      fill: #E5E7EB;
      stroke: #9CA3AF;
      stroke-width: 2;
   }

   .lock-body {
      fill: #9CA3AF;
   }

   .lock-shackle {
      fill: none;
      stroke: #9CA3AF;
      stroke-width: 3;
      stroke-linecap: round;
   }

   @media (max-width: 991.98px) {
      .svg-scroll-container > div[x-data] {
         min-width: 940px;
      }

      .building-label {
         font-size: 18px;
      }
   }
</style>

<div>
   <!-- Created with Inkscape (http://www.inkscape.org/) -->

   <div x-data>
   <svg
      width="1920mm"
      height="1080mm"
      viewBox="0 0 1920 1080"
      version="1.1"
      id="svg1"
      sodipodi:docname="Phase 2 - All Buildings.svg"
      inkscape:version="1.4.2 (f4327f4, 2025-05-13)"
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
      inkscape:cx="3551.5218"
      inkscape:cy="1635.5327"
      inkscape:window-width="1920"
      inkscape:window-height="974"
      inkscape:window-x="-11"
      inkscape:window-y="-11"
      inkscape:window-maximized="1"
      inkscape:current-layer="svg1" />
   <defs
      id="defs1" />
   <rect
      style="fill:#fcfcfc;stroke:#000000;stroke-width:6.42017"
      id="rect5"
      width="1084.8386"
      height="1054.7672"
      x="417.95612"
      y="12.597302" />
   <rect class="campus-land" x="470" y="70" width="250" height="170" rx="42" />
   <rect class="campus-land" x="1160" y="120" width="260" height="210" rx="56" />
   <rect class="campus-land" x="510" y="780" width="280" height="190" rx="48" />
   <rect class="campus-land" x="1140" y="760" width="300" height="210" rx="52" />
   <rect class="campus-path" x="700" y="480" width="520" height="44" rx="20" />
   <rect class="campus-path" x="930" y="230" width="50" height="650" rx="20" />
   <circle class="campus-tree" cx="620" cy="170" r="10" />
   <circle class="campus-tree" cx="655" cy="136" r="8" />
   <circle class="campus-tree" cx="1268" cy="190" r="9" />
   <circle class="campus-tree" cx="1310" cy="150" r="7" />
   <circle class="campus-tree" cx="560" cy="850" r="10" />
   <circle class="campus-tree" cx="1248" cy="890" r="9" />
   <g
      inkscape:label="Layer 1"
      inkscape:groupmode="layer"
      id="layer1">
      <g
         id="g14"
         class="building"
         @click="Livewire.dispatch('buildingSelected',{buildingName:'Forge'})"
         transform="matrix(1.1340906,0,0,1.0388244,-534.8085,0.45987971)">
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
         <circle class="building-icon" cx="1391" cy="168" r="14" />
         <path class="building-icon-mark" d="M 1385 174 v-10 h12 v10 z M 1389 174 v-4 M 1393 174 v-4 M 1387 168 h8" />
         <text class="building-label" x="1391" y="238">Forge</text>
      </g>
      <g
         id="g12"
         class="building"
         @click="Livewire.dispatch('buildingSelected',{buildingName:'OvalBuilding'})"
         transform="matrix(1.1430264,-0.03202256,0.02003943,0.60017012,-724.68833,265.77938)">
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
         <circle class="building-icon" cx="1627" cy="382" r="16" />
         <path class="building-icon-mark" d="M 1621 388 v-12 h12 v12 z M 1625 388 v-4 M 1629 388 v-4 M 1623 382 h8" />
         <text class="building-label" x="1629" y="415">OVB</text>
      </g>
      <g
         id="g16"
         class="building-banned"
         @click="Livewire.dispatch('buildingSelected',{buildingName:'Auditorium'})"
         transform="matrix(1.1340906,0,0,1.0388244,-623.23982,-54.92406)">
         <rect
            style="fill:#fcfcfc;stroke:#000000;stroke-width:4.64797"
            id="rect10"
            width="215.79761"
            height="112.94045"
            x="1214.5502"
            y="468.1012" />
         <circle class="lock-icon" cx="1322" cy="510" r="16" />
         <rect class="lock-body" x="1313" y="511" width="18" height="14" rx="2" />
         <path class="lock-shackle" d="M 1316 510 v -5 a 6 6 0 0 1 12 0 v 5" />
         <text class="building-label" x="1322" y="560">Auditorium</text>
      </g>
      <g
         id="g20"
         class="building"
         @click="Livewire.dispatch('buildingSelected',{buildingName:'MSB'})"
         transform="matrix(1.9051426,0,0,1.6176777,-1881.0889,-134.85093)">
         <rect
            style="fill:#fcfcfc;stroke:#000000;stroke-width:5.91496"
            id="rect12"
            width="99.918365"
            height="138.12245"
            x="1636.8979"
            y="208.65306" />
         <circle class="building-icon" cx="1688" cy="244" r="12" />
         <path class="building-icon-mark" d="M 1683 248 v-9 h10 v9 z M 1686 248 v-3 M 1689 248 v-3 M 1684 243 h8" />
         <text class="building-label" x="1687" y="300">MSB</text>
      </g>
      <g
         id="g15"
         class="building"
         @click="Livewire.dispatch('buildingSelected',{buildingName:'Library'})"
         transform="matrix(1.1340906,0,0,1.0388244,-635.73522,-153.85585)">
         <rect
            style="fill:#fcfcfc;stroke:#000000;stroke-width:5.30342"
            id="rect9"
            width="196.04012"
            height="181.34624"
            x="994.46973"
            y="393.49017" />
         <circle class="building-icon" cx="1092" cy="444" r="16" />
         <path class="building-icon-mark" d="M 1086 450 v-12 h12 v12 z M 1090 450 v-4 M 1094 450 v-4 M 1088 444 h8" />
         <text class="building-label" x="1092" y="515">Library</text>
      </g>
      <g
         id="g17"
         class="building"
         @click="Livewire.dispatch('buildingSelected',{buildingName:'STMB'})"
         transform="matrix(1.1340906,0,0,1.0388244,-607.07547,-0.30026493)">
         <rect
            style="fill:#fcfcfc;stroke:#000000;stroke-width:4.36356"
            id="rect8"
            width="223.42894"
            height="174.93916"
            x="1314.3264"
            y="620.77533" />
         <circle class="building-icon" cx="1426" cy="674" r="16" />
         <path class="building-icon-mark" d="M 1420 680 v-12 h12 v12 z M 1424 680 v-4 M 1428 680 v-4 M 1422 674 h8" />
         <text class="building-label" x="1426" y="744">STMB</text>
      </g>
      <g
         id="g19"
         class="building"
         @click="Livewire.dispatch('buildingSelected',{buildingName:'SBS'})"
         transform="matrix(1.1340906,0,0,1.0388244,-624.2471,-10.196326)">
         <rect
            style="fill:#fcfcfc;stroke:#000000;stroke-width:3.4489"
            id="rect6"
            width="416.8334"
            height="152.3436"
            x="990.6037"
            y="848.07306" />
         <circle class="building-icon" cx="1200" cy="900" r="16" />
         <path class="building-icon-mark" d="M 1194 906 v-12 h12 v12 z M 1198 906 v-4 M 1202 906 v-4 M 1196 900 h8" />
         <text class="building-label" x="1200" y="958">SBS</text>
      </g>
      <g
         id="g18"
         class="building"
         @click="Livewire.dispatch('buildingSelected',{buildingName:'STC'})"
         transform="matrix(1.1340906,0,0,1.0388244,-626.83243,-23.550553)">
         <rect
            style="fill:#fcfcfc;stroke:#000000;stroke-width:3.93309"
            id="rect7"
            width="248.83904"
            height="200.34921"
            x="995.25403"
            y="620.56012" />
         <circle class="building-icon" cx="1120" cy="680" r="16" />
         <path class="building-icon-mark" d="M 1114 686 v-12 h12 v12 z M 1118 686 v-4 M 1122 686 v-4 M 1116 680 h8" />
         <text class="building-label" x="1120" y="756">STC</text>
      </g>
   </g>
   </svg>
   </div>

</div>