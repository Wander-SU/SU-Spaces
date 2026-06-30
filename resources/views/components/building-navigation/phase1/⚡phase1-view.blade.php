<?php

use Livewire\Component;
use Livewire\Attributes\On;

new class extends Component{
   public array $roomStatuses = [];

   public function render(){
      return view('components.building-navigation.phase1.⚡phase1-view');
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

<style>
   .svg-scroll-container {
      background: #F5F6F8;
      border: 1px solid #D9D9D9 !important;
      border-radius: 16px;
      padding: 12px;
   }

   .svg-scroll-container > div[x-data] {
      min-width: 980px;
   }

   #svg1 {
      display: block;
      width: 100%;
      height: auto;
      background: transparent;
   }

   .phase1-land {
      fill: #E9F7EE;
   }

   .phase1-path {
      fill: #EBEEF2;
   }

   .phase1-tree {
      fill: #BDE3C5;
   }

   .building-section {
      cursor: pointer;
      fill: #FFFFFF !important;
      stroke: #D9D9D9 !important;
      stroke-width: 2.4 !important;
      filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.08));
      transition: all .2s ease;
      transform-box: fill-box;
      transform-origin: center;
   }

   .building-section:hover {
      stroke: #0D6EFD !important;
      filter: drop-shadow(0 8px 18px rgba(13, 110, 253, 0.18));
      transform: translateY(-4px);
   }

   .building-section:active {
      stroke: #0D6EFD !important;
      filter: drop-shadow(0 0 0 2px rgba(13, 110, 253, 0.2)) drop-shadow(0 10px 20px rgba(13, 110, 253, 0.2));
   }

   .phase1-label,
   .phase1-icon,
   .phase1-icon-mark {
      pointer-events: none;
   }

   .phase1-label {
      fill: #3D4956;
      font-size: 28px;
      font-weight: 700;
      letter-spacing: 0.6px;
      text-anchor: middle;
   }

   .phase1-label-compact {
      font-size: 17px;
      letter-spacing: 0.2px;
   }

   .phase1-icon {
      fill: #EAF2FF;
      stroke: #0D6EFD;
      stroke-width: 2;
   }

   .phase1-icon-mark {
      fill: none;
      stroke: #0D6EFD;
      stroke-width: 2;
      stroke-linecap: round;
      stroke-linejoin: round;
   }

   @media (max-width: 991.98px) {
      .svg-scroll-container > div[x-data] {
         min-width: 900px;
      }

      .phase1-label {
         font-size: 24px;
      }
   }
</style>

<div>
   <div x-data>
      <svg
         width="1920mm"
         height="1080mm"
         viewBox="0 0 1920 1080"
         version="1.1"
         id="svg1"
         sodipodi:docname="Phase 1- Central Building.svg"
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
         inkscape:zoom="0.1157493"
         inkscape:cx="4151.2129"
         inkscape:cy="1568.044"
         inkscape:window-width="1920"
         inkscape:window-height="974"
         inkscape:window-x="-11"
         inkscape:window-y="-11"
         inkscape:window-maximized="1"
         inkscape:current-layer="svg1"
         showguides="false" />
      <defs
         id="defs1" />
      <rect class="phase1-land" x="220" y="180" width="380" height="190" rx="48" />
      <rect class="phase1-land" x="1240" y="170" width="320" height="220" rx="54" />
      <rect class="phase1-land" x="640" y="760" width="560" height="170" rx="58" />
      <rect class="phase1-path" x="640" y="300" width="90" height="540" rx="28" />
      <rect class="phase1-path" x="730" y="470" width="540" height="62" rx="24" />
      <circle class="phase1-tree" cx="312" cy="258" r="11" />
      <circle class="phase1-tree" cx="1360" cy="252" r="10" />
      <circle class="phase1-tree" cx="830" cy="828" r="10" />
      <g
         id="g4"
         transform="matrix(2.1023051,0,0,2.2403163,-350.82887,-381.37199)">
         <rect
            style="fill:#fcfcfc;stroke:#000000;stroke-width:4.94071"
            id="rect1"
            class="building-section"
            @click="Livewire.dispatch('partSelected',{partName:'CentralPart'})"
            width="587.18597"
            height="133.20139"
            x="202.29834"
            y="482.95038" />
         <circle class="phase1-icon" cx="493" cy="550" r="16" />
         <path class="phase1-icon-mark" d="M 485 550 h 16 M 493 542 v 16" />
         <text class="phase1-label" x="493" y="605">Central Part</text>
         <rect
            style="fill:#fcfcfc;stroke:#000000;stroke-width:6.97274"
            id="rect2"
            class="building-section"
            @click="Livewire.dispatch('partSelected',{partName:'LeftWing'})"
            width="134.12589"
            height="273.71774"
            x="204.77379"
            y="207.71255" />
         <circle class="phase1-icon" cx="272" cy="302" r="13" />
         <path class="phase1-icon-mark" d="M 266 296 h 12 M 266 302 h 12 M 266 308 h 12" />
         <text class="phase1-label phase1-label-compact" x="272" y="338">
            <tspan x="272" dy="0">Left</tspan>
            <tspan x="272" dy="22">Wing</tspan>
         </text>
         <rect
            style="fill:#fcfcfc;stroke:#000000;stroke-width:6.88202"
            id="rect2-8"
            class="building-section"
            @click="Livewire.dispatch('partSelected',{partName:'RightWing'})"
            width="134.21661"
            height="266.46152"
            x="654.97906"
            y="211.06674" />
         <circle class="phase1-icon" cx="722" cy="302" r="13" />
         <path class="phase1-icon-mark" d="M 716 296 h 12 M 722 296 v 12 M 716 308 h 12" />
         <text class="phase1-label phase1-label-compact" x="722" y="338">
            <tspan x="722" dy="0">Right</tspan>
            <tspan x="722" dy="22">Wing</tspan>
         </text>
      </g>
      <g
         inkscape:label="Layer 1"
         inkscape:groupmode="layer"
         id="layer1" />
      </svg>
   </div>
</div>