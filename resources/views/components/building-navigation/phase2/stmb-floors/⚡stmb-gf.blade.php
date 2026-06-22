<?php

use Livewire\Component;
use Livewire\Attributes\On;

new class extends Component{
   public array $roomStatuses = [];

   public function render(){
      return view('components.building-navigation.phase2.stmb-floors.⚡stmb-gf');
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
         sodipodi:docname="STMB - GF.svg"
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
         inkscape:cx="4072.2807"
         inkscape:cy="2446.51"
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
            @click="Livewire.dispatch('roomSelected',{roomName:'gf-01'})"
            transform="matrix(0.7776267,0,0,1.1796938,329.97088,-16.560587)">
            <rect
               style="fill:{{ $this->roomColor('STMB GF-01') }};stroke:#000000;stroke-width:1.565"
               id="rect2"
               width="1072.2625"
               height="324.17239"
               x="401.05942"
               y="45.716618" />
         </g>
         <g
            id="g7"
            class="room"
            @click="Livewire.dispatch('roomSelected',{roomName:'gf-02'})"
            transform="matrix(1.5865436,0,0,0.99930243,45.573449,0.58997446)">
            <rect
               style="fill:{{ $this->roomColor('STMB GF-02') }};stroke:#000000;stroke-width:1.07625"
               id="rect3"
               width="524.15179"
               height="399.47006"
               x="375.87869"
               y="646.02234"
               ry="0" />
         </g>
      </g>
      </svg>
   </div>
</div>