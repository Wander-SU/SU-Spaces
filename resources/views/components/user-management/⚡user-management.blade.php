<?php

use App\Models\Role;
use App\Models\Room;
use App\Models\User;
use App\Rules\AlphaSpaces;
use App\Rules\CourseSessionData;
use App\Rules\DateValid;
use App\Rules\DayOfWeek;
use App\Rules\TwoNames;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;
    protected $paginationTheme = "bootstrap";
    public $search;
    public $filterRole = "";
    public $filterFaculty = "";
    public $filterCourse = "";
    public $orderDirection1 = "asc";
    public $orderDirection2 = "asc";
    public $id;
    public $smallDevice=False;
    public $name;
    public $email;
    public $email_verified_at;
    public $active;
    public $role_id;
    public $orderField;
    public $showForm = false;
    public $isEditing = false;
    public $admission_or_employee;
    public $faculty;
    public $course;

    private const FACULTIES = ['SCES', 'SIMS', 'SLS', 'SBS', 'STH', 'SHSS'];

    private const COURSES_BY_FACULTY = [
      'SCES' => ['BICS', 'BCNS', 'BBIT', 'BSEEE'],
      'SIMS' => ['BBS.FENG', 'BBS.FE', 'BBS.ACT', 'BSc.SDS'],
      'SLS' => ['LLB'],
      'SBS' => ['BFS', 'BSCM', 'BCOM'],
      'STH' => ['BTM', 'BHM'],
      'SHSS' => ['BDP', 'BAC', 'BIS'],
    ];

    public function render()
    {
      // Get Room Details
      $roles = Role::all();
      $faculties = User::query()
        ->whereNotNull('faculty')
        ->select('faculty')
        ->distinct()
        ->orderBy('faculty')
        ->pluck('faculty');

      $courses = User::query()
        ->whereNotNull('course')
        ->when($this->filterFaculty !== "", function ($q) {
          $q->where('faculty', $this->filterFaculty);
        })
        ->select('course')
        ->distinct()
        ->orderBy('course')
        ->pluck('course');
      
      // Get Base Bookings
      $users = User::query()
      ->join('roles','users.role_id','=','roles.id')
      ->where(function($q){
        $q->where('roles.role_name','like',"%{$this->search}%")
        ->orWhere('users.name','like',"%{$this->search}%")
        ->orWhere('users.email','like',"%{$this->search}%");
      })
      ->when($this->filterRole !== "", function ($q) {
        $q->where('roles.id', (int) $this->filterRole);
      })
      ->when($this->filterFaculty !== "", function ($q) {
        $q->where('users.faculty', $this->filterFaculty);
      })
      ->when($this->filterCourse !== "", function ($q) {
        $q->where('users.course', $this->filterCourse);
      })
      ->select('users.*','roles.role_name')
      ->orderBy('users.name',$this->orderDirection1)
      ->orderBy('roles.role_name',$this->orderDirection2)
      ->paginate(env('PAGINATION_COUNT',50));
      
      return view('components.user-management.⚡user-management',compact('users','roles','faculties','courses'));
    }

    public function updatedFilterFaculty(){
        $this->filterCourse = "";
        $this->resetPage();
    }

    public function updatedFilterRole(){
        $this->resetPage();
    }

    public function updatedFilterCourse(){
        $this->resetPage();
    }

    public function updatedOrderDirection1(){
        $this->resetPage();
    }

    public function orderBy($field)
    {
        //Update the orderfield
        $this->orderField = $field;

        if($this->orderField=="user_name"){
          if($this->orderDirection1 == "asc"){
            $this->orderDirection1 = "desc";
          }
          else{
            $this->orderDirection1 = "asc";
          }
        }

        if($this->orderField=="role_name"){
          if($this->orderDirection2 == "asc"){
            $this->orderDirection2= "desc";
          }
          else{
            $this->orderDirection2 = "asc";
          }
        }
    }

    /**
     * Resetting the search
     */
    public function clearSearch(){
        $this->search = "";
      $this->filterRole = "";
      $this->filterFaculty = "";
      $this->filterCourse = "";
      $this->orderDirection1 = "asc";
    }

    /**
     * Reset the form
     * Set show form to true
     */
    public function add(){
        $this->reset();
        $this->showForm = true;
    }

    public function cancel(){
        $this->reset();
        $this->showForm = false;
    }

    /**
     * Ban/Activate User
    */
    public function regulate($id)
    {
        $user=User::findorfail($id);
        if($user->active==1){
            $user->active=0;
            $user->save();
            session()->flash('warning','User Banned Successfully');
        }
        else{
            $user->active=1;
            $user->save();
            session()->flash('success','User Activated Successfully');
        }
    }

    /**
     * Show the form on small devices
    */
    public function showHidden(){
      if($this->smallDevice==False){
        $this->smallDevice = True;
      }
      else{
        $this->smallDevice = False;
      }
    }

    /**
     * Validation rules
     */
    public function rules(){
        $course_data = new CourseSessionData();
        return [
            'role_id' => ['required'],
            'name' => ['required',new TwoNames()],
            'active' => ['required'],
        'email' => ['required','email'],
        'faculty' => [
          'nullable',
          Rule::in(self::FACULTIES),
          function (string $attribute, mixed $value, \Closure $fail): void {
            if ($this->selectedRoleName() === 'lecturer' && blank((string) $value)) {
              $fail('Faculty is required for lecturers.');
            }
          },
        ],
        'course' => [
          'nullable',
          function (string $attribute, mixed $value, \Closure $fail): void {
            if (($this->faculty ?? '') === '' || ($value ?? '') === '') {
              return;
            }

            $allowedCourses = self::COURSES_BY_FACULTY[(string) $this->faculty] ?? [];
            if (! in_array((string) $value, $allowedCourses, true)) {
              $fail('Select a valid course for the selected faculty.');
            }
          },
        ],
        ];
    }

    private function selectedRoleName(): string
    {
      $role = Role::find((int) $this->role_id);

      return strtolower((string) ($role->role_name ?? ''));
    }

    /**
     * Store the values 
     * Doesn't do anything, admins cannot create new users
    */
    public function store()
    {
        // 
    }

    /**
     * To edit
     */
    public function edit($id)
    {
        // Select based on id
        $user = User::findOrFail($id);
        $this->id = $id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->active = $user->active;
        $this->role_id = $user->role_id;
        $this->admission_or_employee = $user->admission_number ?: ($user->employee_id ?: '');
        $this->faculty = $user->faculty;
        $this->course = $user->course;
        $this->showForm = true;
        $this->isEditing = true;
    }

    /**
     * Update method
     */
    public function update($id)
    {
        // Validate
        $this->validate();

        // Get the user needed
        $user =User::findOrFail($id);

        $user->name = $this->name;
        $user->email = $this->email;
        $user->active = $this->active;
        $user->role_id = $this->role_id;
        $user->faculty = $this->faculty ?: null;
        $user->course = $this->course ?: null;
        $user->updated_at = now();
        $user->save();

        // Update the state of our variables
        $this->reset();
        $this->isEditing=false;
        $this->showForm=false;
        
        session()->flash('success','User Updated Successfully');
            
    }

    /**
     * Delete an item
     * Cannot Delete Users, it is a foreign Key in the bookings table
     */
    public function destroy($id)
    {
        try{
            $user = User::findOrFail($id);
            $user->delete();

            // Return to the page and wipe everything.
            // return redirect()->route('baseBookings.index')->with('success','Base Booking Deleted Successfully');

            // Return to the page and retain search terms
            session()->flash('success','User Deleted Successfully');
        }catch(Exception $e){
            Log::error($e);
            session()->flash('error','Cannot Delete this User');
        }
    }
}
?>

<div x-data="{ drawerOpen: false, userForm: { id: '', name: '', admissionOrEmployee: '', email: '', role: '', status: '', faculty: '', course: '' } }" class="w-full px-4 sm:px-8 max-w-none bg-[#F2E6D9] dark:bg-[#0a0a0a] min-h-screen font-sans">
    {{-- Root Element: Livewire views need this --}}

    {{-- Show the messages --}}
    @if (session()->has('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    @if (session()->has('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    @if (session()->has('warning'))
      <div class="alert alert-warning alert-dismissible fade show" role="alert">
        {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif
    
    {{-- Table --}}
    <div>
      <div class="bg-white dark:bg-transparent border border-[#1d2d54]/20 dark:border-[#1d2d54]/30 rounded-xl p-4 mb-6 shadow-xs flex flex-wrap items-center justify-between gap-4">
          <h3 class="m-0 text-base font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">All Users</h3>
          <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">

          {{-- Search form --}}
          <form class="d-none d-md-inline-block me-2">
              <div class="input-group input-group-sm">
                  {{--  show inline error messages --}}
                  <input wire:model.live.debounce.700ms="search" type="text" name="search"
                    class="w-full sm:w-64 rounded-lg border border-[#1d2d54]/20 dark:border-[#1d2d54]/30 bg-white dark:bg-transparent px-3 py-2 text-sm text-[#1b1b18] dark:text-[#EDEDEC] focus:outline-none {{ $errors->has('search') ? 'is-invalid' : '' }}"
                    placeholder="Search Base Bookings" 
                    autofocus>
                    @error('search')
                      <div class="invalid-feedback">
                        {{ $message }}
                      </div>
                    @enderror
              </div>
          </form>

          <form class="d-none d-md-inline-block me-2">
              <select wire:model.live="filterRole" name="filter_role"
                class="w-full sm:w-44 rounded-lg border border-[#1d2d54]/20 dark:border-[#1d2d54]/30 bg-white dark:bg-transparent px-3 py-2 text-sm text-[#1b1b18] dark:text-[#EDEDEC] focus:outline-none">
                <option value="">All Roles</option>
                @foreach ($roles as $role)
                  <option value="{{ $role->id }}">{{ $role->role_name }}</option>
                @endforeach
              </select>
          </form>

          <form class="d-none d-md-inline-block me-2">
              <select wire:model.live="filterFaculty" name="filter_faculty"
                class="w-full sm:w-44 rounded-lg border border-[#1d2d54]/20 dark:border-[#1d2d54]/30 bg-white dark:bg-transparent px-3 py-2 text-sm text-[#1b1b18] dark:text-[#EDEDEC] focus:outline-none">
                <option value="">All Faculties</option>
                @foreach ($faculties as $faculty)
                  <option value="{{ $faculty }}">{{ $faculty }}</option>
                @endforeach
              </select>
          </form>

          <form class="d-none d-md-inline-block me-2">
              <select wire:model.live="filterCourse" name="filter_course"
                class="w-full sm:w-44 rounded-lg border border-[#1d2d54]/20 dark:border-[#1d2d54]/30 bg-white dark:bg-transparent px-3 py-2 text-sm text-[#1b1b18] dark:text-[#EDEDEC] focus:outline-none">
                <option value="">All Courses</option>
                @foreach ($courses as $course)
                  <option value="{{ $course }}">{{ $course }}</option>
                @endforeach
              </select>
          </form>

          <a href="#" wire:click="orderBy('user_name')"
            class="d-none d-md-inline-flex items-center justify-center rounded-lg border border-[#1d2d54]/20 dark:border-[#1d2d54]/30 px-3 py-2 text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] hover:bg-[#f5f5f4] dark:hover:bg-[#1c1c1b]"
            title="Sort Name">
            @if($orderDirection1=="asc")
              <i class="bi bi-sort-alpha-up"></i>
            @else
              <i class="bi bi-sort-alpha-down"></i>
            @endif
          </a>
          
          {{-- Link to reset --}}
          <a href="#"  wire:click="clearSearch"
            class="d-md-inline-flex d-none items-center justify-center rounded-lg border border-[#1d2d54]/20 dark:border-[#1d2d54]/30 px-3 py-2 text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] hover:bg-[#f5f5f4] dark:hover:bg-[#1c1c1b]" 
            title="Reset">
            <i class="bi bi-arrow-clockwise"></i>
          </a>
          
          {{-- Link to show the filter icon--}}
          <a href="#"  wire:click="showHidden"
            class="d-md-none d-inline-flex items-center justify-center rounded-lg border border-[#1d2d54]/20 dark:border-[#1d2d54]/30 px-3 py-2 text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] hover:bg-[#f5f5f4] dark:hover:bg-[#1c1c1b]" 
            title="Show Hidden">            
            <i class="bi bi-funnel-fill"></i>
          </a>
          </div>
      </div>      

      @if($this->smallDevice)
          <div class="d-block ms-2 row">
            {{-- Search form --}}
            <div class="col-md-4 mt-4">
              <div class="col-md-4 my-2 me-2">
                <form class="me-2 pt-4">
                    <div class="input-group input-group-sm">
                        {{--  show inline error messages --}}
                        <input wire:model.live.debounce.700ms="search" type="text" name="search"
                          class="form-control {{ $errors->has('search') ? 'is-invalid' : '' }}"
                          placeholder="Search User" >
                          @error('search')
                            <div class="invalid-feedback">
                              {{ $message }}
                            </div>
                          @enderror
                    </div>
                </form>
              </div>

              <div class="col-md-4 my-2 me-2">
                <select wire:model.live="filterRole" name="filter_role" class="form-control">
                  <option value="">All Roles</option>
                  @foreach ($roles as $role)
                    <option value="{{ $role->id }}">{{ $role->role_name }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-4 my-2 me-2">
                <select wire:model.live="filterFaculty" name="filter_faculty" class="form-control">
                  <option value="">All Faculties</option>
                  @foreach ($faculties as $faculty)
                    <option value="{{ $faculty }}">{{ $faculty }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-4 my-2 me-2">
                <select wire:model.live="filterCourse" name="filter_course" class="form-control">
                  <option value="">All Courses</option>
                  @foreach ($courses as $course)
                    <option value="{{ $course }}">{{ $course }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-4 my-2 me-2">
                <a href="#" wire:click="orderBy('user_name')"
                  class="btn btn-sm btn-outline-secondary"
                  title="Sort Name">
                  @if($orderDirection1=="asc")
                    <i class="bi bi-sort-alpha-up"></i>
                  @else
                    <i class="bi bi-sort-alpha-down"></i>
                  @endif
                </a>
              </div>

              {{-- Link to reset --}}
              <div class="col-md-4 my-2 me-2">
                <a href="#"  wire:click="clearSearch"
                  class="btn btn-success btn-sm" 
                  title="Reset">
                  <i class="bi bi-arrow-clockwise"></i>
                  Reset Search
                </a>
              </div>
            </div>
          </div>
      @endif

      @if(count($users)!=0)
        <div class="w-full bg-transparent border border-[#1d2d54]/10 rounded-xl overflow-hidden shadow-xs mt-4">
        <div class="quick-access-table-wrap table-responsive">
          <table class="w-full text-sm font-sans border-collapse">
            <thead>
              <tr class="bg-[#941c1c] text-white font-sans text-sm tracking-wide font-semibold text-left">
                <th class="px-3 py-3" style="width: 10px">Number</th>
                <th class="px-3 py-3">
                    <a href="#" wire:click="orderBy('user_name')">
                        User Name
                    </a>
                    @if($orderDirection1=="asc")
                        <i class="bi bi-sort-alpha-up"></i>
                    @else
                        <i class="bi bi-sort-alpha-down"></i>
                    @endif
                </th>
                <th class="px-3 py-3">
                    <a href="#" wire:click="orderBy('role_name')">
                        Role Name
                    </a>
                    @if($orderDirection2=="asc")
                        <i class="bi bi-sort-alpha-up"></i>
                    @else
                        <i class="bi bi-sort-alpha-down"></i>
                    @endif
                </th>
                <th class="px-3 py-3">Admission Number / Employee ID</th>
                <th class="px-3 py-3">Faculty and Course</th>
                <th class="px-3 py-3">User Email</th>
                <th class="px-3 py-3">User Status</th>
                <th class="px-3 py-3">Actions</th>
              </tr>
            </thead>        
            <tbody class="bg-white/40 dark:bg-[#161615]/40 backdrop-blur-md">
              @php
                $facultyAbbrevMap = [
                  'School of Computing and Engineering Science (SCES)' => 'SCES',
                  'School of Computing and Engineering Science' => 'SCES',
                  'Strathmore Institute of Mathematical Sciences (SIMS)' => 'SIMS',
                  'Strathmore Institute of Mathematical Sciences' => 'SIMS',
                  'Strathmore Law School (SLS)' => 'SLS',
                  'Strathmore Law School' => 'SLS',
                  'Strathmore Business School (SBS)' => 'SBS',
                  'Strathmore Business School' => 'SBS',
                  'School of Tourism and Hospitality (STH)' => 'STH',
                  'School of Tourism and Hospitality' => 'STH',
                  'School of Humanities and Social Sciences (SHSS)' => 'SHSS',
                  'School of Humanities and Social Sciences' => 'SHSS',
                  'SI' => 'SIMS',
                ];

                $courseAbbrevMap = [
                  'Bachelor of Informatics and Computer Science (BICS)' => 'BICS',
                  'Bachelor of Informatics and Computer Science' => 'BICS',
                  'Bachelor of Cyber Networks and Security (BCNS)' => 'BCNS',
                  'Bachelor of Cyber Networks and Security' => 'BCNS',
                  'Bachelor of Business Information and Technology (BBIT)' => 'BBIT',
                  'Bachelor of Business Information and Technology' => 'BBIT',
                  'Bachelor of Science in Electrical and Electronics Engineering (BSEEE)' => 'BSEEE',
                  'Bachelor of Science in Electrical and Electronics Engineering' => 'BSEEE',
                  'Bachelor of Business Science in Financial Engineering (BBS.FENG)' => 'BBS.FENG',
                  'Bachelor of Business Science in Financial Engineering' => 'BBS.FENG',
                  'Bachelor of Business Science in Financial Economics (BBS.FE)' => 'BBS.FE',
                  'Bachelor of Business Science in Financial Economics' => 'BBS.FE',
                  'Bachelor of Business Science in Acturial Science (BBS.ACT)' => 'BBS.ACT',
                  'Bachelor of Business Science in Acturial Science' => 'BBS.ACT',
                  'Bachelor of Business Science in Actuarial Science' => 'BBS.ACT',
                  'Bachelor of Science in Statistics and Data Science (BSc.SDS)' => 'BSc.SDS',
                  'Bachelor of Science in Statistics and Data Science' => 'BSc.SDS',
                  'Bachelor of Laws (LLB)' => 'LLB',
                  'Bachelor of Laws' => 'LLB',
                  'Bachelor of Financial Services (BFS)' => 'BFS',
                  'Bachelor of Financial Services' => 'BFS',
                  'Bachelor of Supply Chain and Operations Management (BSCM)' => 'BSCM',
                  'Bachelor of Supply Chain and Operations Management' => 'BSCM',
                  'Bachelor of Commerce (BCOM)' => 'BCOM',
                  'Bachelor of Commerce' => 'BCOM',
                  'Bachelor of Science in Tourism Management (BTM)' => 'BTM',
                  'Bachelor of Science in Tourism Management' => 'BTM',
                  'Bachelor of Science in Hospitality Management (BHM)' => 'BHM',
                  'Bachelor of Science in Hospitality Management' => 'BHM',
                  'Bachelor of Development and Philosophy (BDP)' => 'BDP',
                  'Bachelor of Development and Philosophy' => 'BDP',
                  'Bachelor of Arts in Communication (BAC)' => 'BAC',
                  'Bachelor of Arts in Communication' => 'BAC',
                  'Bachelor of International Studies (BIS)' => 'BIS',
                  'Bachelor of International Studies' => 'BIS',
                ];
              @endphp
              @foreach($users as $user)
              @php
                $displayFaculty = $facultyAbbrevMap[$user->faculty] ?? $user->faculty;
                $displayCourse = $courseAbbrevMap[$user->course] ?? $user->course;
              @endphp
              <tr class="border-b border-[#1d2d54]/5 hover:bg-white/30 transition-colors">
                {{-- Can have {{ $loop->iteration }} --}}
                <td class="px-3 py-3">{{$loop->iteration}}</td>
                <td class="px-3 py-3">{{ trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: $user->name }}</td>
                <td class="px-3 py-3"><span class="font-mono font-semibold text-xs bg-[#c99d3b]/10 text-[#c99d3b] border border-[#c99d3b]/30 px-2.5 py-1 rounded-md">{{$user->role->role_name}}</span></td>
                <td class="px-3 py-3">{{ $user->admission_number ?: ($user->employee_id ?: '-') }}</td>
                <td class="px-3 py-3">
                  @if($user->faculty || $user->course)
                    {{ $displayFaculty ?: '-' }} / {{ $displayCourse ?: '-' }}
                  @else
                    -
                  @endif
                </td>
                <td class="px-3 py-3">{{$user->email}}</td>
                <td class="px-3 py-3"><span class="{{ $user->active==1 ? 'badge bg-success' : 'badge bg-warning text-dark' }}">
                    {{$user->active ? "Active" : "Banned"}}
                </span>
                </td>
                <td class="px-3 py-3">
                  <div class="btn-group" role="group">
                    @if($user->active==1)
                      <a href="#" wire:click="regulate({{$user->id}})" wire:confirm="Are you sure you want to ban this user?"
                        class="bg-[#941c1c] text-white hover:bg-gradient-to-r hover:from-[#F11D22] hover:to-[#FFCC00] hover:text-[#1b1b18] transition-colors text-xs font-medium py-2 px-3 rounded-lg"
                        title="Ban User">
                          <i class="bi bi-ban"></i>
                      </a>
                    @else
                      <a href="#" wire:click="regulate({{$user->id}})"
                        class="bg-[#941c1c] text-white hover:bg-gradient-to-r hover:from-[#F11D22] hover:to-[#FFCC00] hover:text-[#1b1b18] transition-colors text-xs font-medium py-2 px-3 rounded-lg"
                        title="Activate User">
                          <i class="bi bi-hand-thumbs-up-fill"></i>
                      </a>
                    @endif
                    <a href="#" wire:click="edit({{$user->id}})"
                      x-on:click.prevent="drawerOpen = true; userForm = { id: @js((string)$user->id), name: @js(trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: $user->name), admissionOrEmployee: @js($user->admission_number ?: ($user->employee_id ?: '')), email: @js($user->email), role: @js((string) $user->role_id), status: @js((string) $user->active), faculty: @js((string) ($user->faculty ?? '')), course: @js((string) ($user->course ?? '')) }"
                      class="bg-[#941c1c] text-white hover:bg-gradient-to-r hover:from-[#F11D22] hover:to-[#FFCC00] hover:text-[#1b1b18] transition-colors text-xs font-medium py-2 px-3 rounded-lg" 
                      title="Edit">
                      <i class="bi bi-pencil"></i>
                    </a>
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        </div>
      @else
        <tbody>
          <p class="text-danger">No User, Role or Email matches the search key</p>
        </tbody>
      @endif

      <div class="mt-4 px-1" data-bs-theme="light">
        {{ $users->links('pagination::bootstrap-5') }}
      </div>
    </div>

    <div class="fixed top-0 right-0 h-full w-80 sm:w-96 z-50 bg-[#02338D]/95 backdrop-blur-md text-white shadow-2xl p-6 border-l border-[#02338D]"
      x-show="drawerOpen"
      style="display: none;">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs font-sans text-gray-300 tracking-wider uppercase">User Profile Details</p>
          <h3 class="text-2xl font-bold font-sans text-white mt-4" x-text="userForm.name"></h3>
        </div>
      </div>

      @if($showForm)
      <form wire:submit="{{"update($id)"}}" class="mt-6">
        @csrf
        @php
          $drawerFaculties = [
            'SCES' => 'School of Computing and Engineering Science (SCES)',
            'SIMS' => 'Strathmore Institute of Mathematical Sciences (SIMS)',
            'SLS' => 'Strathmore Law School (SLS)',
            'SBS' => 'Strathmore Business School (SBS)',
            'STH' => 'School of Tourism and Hospitality (STH)',
            'SHSS' => 'School of Humanities and Social Sciences (SHSS)',
          ];

          $drawerCoursesByFaculty = [
            'SCES' => ['BICS', 'BCNS', 'BBIT', 'BSEEE'],
            'SIMS' => ['BBS.FENG', 'BBS.FE', 'BBS.ACT', 'BSc.SDS'],
            'SLS' => ['LLB'],
            'SBS' => ['BFS', 'BSCM', 'BCOM'],
            'STH' => ['BTM', 'BHM'],
            'SHSS' => ['BDP', 'BAC', 'BIS'],
          ];

          $drawerCourseOptions = $drawerCoursesByFaculty[$this->faculty] ?? [];
        @endphp
        <div class="space-y-4">
          <div>
            <label for="name" class="text-xs font-sans text-gray-300 tracking-wide uppercase font-medium">Name</label>
            <input required wire:model="name" x-model="userForm.name" type="text" name="name" class="w-full bg-[#c99d3b]/20 border border-[#c99d3b]/40 rounded-lg px-3 py-2.5 text-sm text-white placeholder-gray-400 focus:ring-0 focus:border-[#c99d3b] @error('name') is-invalid @enderror" value="{{old('name')}}">
            @error('name')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div>
            <label for="admission_or_employee" class="text-xs font-sans text-gray-300 tracking-wide uppercase font-medium">Admission Number / Employee ID</label>
            <input wire:model="admission_or_employee" x-model="userForm.admissionOrEmployee" type="text" name="admission_or_employee" readonly class="w-full bg-[#c99d3b]/20 border border-[#c99d3b]/40 rounded-lg px-3 py-2.5 text-sm text-white placeholder-gray-400 focus:ring-0 focus:border-[#c99d3b]" value="{{old('admission_or_employee')}}">
          </div>

          <div>
            <label for="email" class="text-xs font-sans text-gray-300 tracking-wide uppercase font-medium">Email</label>
            <input required wire:model="email" x-model="userForm.email" type="email" name="email" class="w-full bg-[#c99d3b]/20 border border-[#c99d3b]/40 rounded-lg px-3 py-2.5 text-sm text-white placeholder-gray-400 focus:ring-0 focus:border-[#c99d3b] @error('email') is-invalid @enderror" value="{{old('email')}}">
            @error('email')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label for="faculty" class="text-xs font-sans text-gray-300 tracking-wide uppercase font-medium">Faculty</label>
              <select wire:model.live="faculty" x-model="userForm.faculty" name="faculty" class="w-full bg-[#c99d3b]/20 border border-[#c99d3b]/40 rounded-lg px-3 py-2.5 text-sm text-white placeholder-gray-400 focus:ring-0 focus:border-[#c99d3b] @error('faculty') is-invalid @enderror">
                <option value="" class="text-black">-- Select Faculty --</option>
                @foreach ($drawerFaculties as $facultyCode => $facultyLabel)
                  <option value="{{ $facultyCode }}" class="text-black">{{ $facultyLabel }}</option>
                @endforeach
              </select>
              @error('faculty')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div>
              <label for="course" class="text-xs font-sans text-gray-300 tracking-wide uppercase font-medium">Course</label>
              <select wire:model="course" x-model="userForm.course" name="course" class="w-full bg-[#c99d3b]/20 border border-[#c99d3b]/40 rounded-lg px-3 py-2.5 text-sm text-white placeholder-gray-400 focus:ring-0 focus:border-[#c99d3b] @error('course') is-invalid @enderror" @disabled(empty($this->faculty))>
                <option value="" class="text-black">-- Select Course --</option>
                @foreach ($drawerCourseOptions as $courseCode)
                  <option value="{{ $courseCode }}" class="text-black">{{ $courseCode }}</option>
                @endforeach
              </select>
              @error('course')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div>
            <label for="role_id" class="text-xs font-sans text-gray-300 tracking-wide uppercase font-medium">Role</label>
            <select required wire:model="role_id" x-model="userForm.role" name="role_id" class="w-full bg-[#c99d3b]/20 border border-[#c99d3b]/40 rounded-lg px-3 py-2.5 text-sm text-white placeholder-gray-400 focus:ring-0 focus:border-[#c99d3b] @error('role_id') is-invalid @enderror" value="{{old('role_id')}}">
              @foreach ( $roles as $role )
                <option value="{{ $role->id }}" class="text-black">{{ $role->role_name }}</option>
              @endforeach
            </select>
            @error('role_id')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div>
            <label for="active" class="text-xs font-sans text-gray-300 tracking-wide uppercase font-medium">Status</label>
            <select required wire:model="active" x-model="userForm.status" name="active" class="w-full bg-[#c99d3b]/20 border border-[#c99d3b]/40 rounded-lg px-3 py-2.5 text-sm text-white placeholder-gray-400 focus:ring-0 focus:border-[#c99d3b] @error('active') is-invalid @enderror" value="{{old('active')}}">
              <option value="1" class="text-black">Active</option>
              <option value="0" class="text-black">Banned</option>
            </select>
            @error('status')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-3">
          <a href="#" wire:click="cancel" x-on:click="drawerOpen = false" class="inline-flex items-center justify-center rounded-lg border border-white/30 px-4 py-2 text-sm font-medium text-white hover:bg-white/10">
            <i class="bi bi-arrow-left"></i> Back
          </a>
        </div>

        <button type="submit" class="w-full bg-white text-[#1d2d54] font-bold font-sans py-3 rounded-lg shadow-md transition duration-150 mt-6 cursor-pointer hover:bg-gradient-to-r hover:from-[#0048AD] hover:to-[#FF383C] hover:text-white">
          <i class="bi-icons bi-save"></i> Save Changes
        </button>
      </form>
      @else
      <p class="mt-6 text-sm text-gray-200">Select a user using the edit button to modify records.</p>
      @endif
    </div>

</div>
