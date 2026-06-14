<?php

use App\Models\Role;
use App\Models\Room;
use App\Models\User;
use App\Rules\AlphaSpaces;
use App\Rules\CourseSessionData;
use App\Rules\DateValid;
use App\Rules\DayOfWeek;
use App\Rules\TwoNames;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;
    protected $paginationTheme = "bootstrap";
    public $search;
    public $orderDirection1 = "asc";
    public $orderDirection2 = "asc";
    public $id;
    public $name;
    public $email;
    public $email_verified_at;
    public $active;
    public $role_id;
    public $orderField;
    public $showForm = false;
    public $isEditing = false;

    public function render()
    {
      // Get Room Details
      $roles = Role::all();
      
      // Get Base Bookings
      $users = User::query()
      ->join('roles','users.role_id','=','roles.id')
      ->where(function($q){
        $q->where('roles.role_name','like',"%{$this->search}%")
        ->orWhere('users.name','like',"%{$this->search}%")
        ->orWhere('users.email','like',"%{$this->search}%");
      })
      ->select('users.*','roles.role_name')
      ->orderBy('users.name',$this->orderDirection1)
      ->orderBy('roles.role_name',$this->orderDirection2)
      ->paginate(env('PAGINATION_COUNT',50));
      
      return view('components.user-management.⚡user-management',compact('users','roles'));
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
     * Validation rules
     */
    public function rules(){
        $course_data = new CourseSessionData();
        return [
            'role_id' => ['required'],
            'name' => ['required',new TwoNames()],
            'active' => ['required'],
            'email' => ['required','email']
        ];
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

<div class="col-md-12">
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
    
    {{-- Form --}}
    @if($showForm)
    <div class="card card-primary card-outline mb-4">
        <div class="card-header">
            <div class="card-title">{{$isEditing ? "Edit" : "Add"}} User</div>
        </div>
        <form wire:submit="{{"update($id)"}}">
            @csrf
            <div class="card-body">
              <div class="row">
                {{-- Role Name --}}
                <div class="col-md-4 mb-3">
                    <label for="role_id" class="form-label">Role Name</label>
                    <select required wire:model="role_id" name="role_id" class="form-control @error('role_id') is-invalid @enderror" value="{{old('role_id')}}">
                      @foreach ( $roles as $role )
                        <option value="{{ $role->id }}">
                          {{ $role->role_name }}
                        </option>
                      @endforeach
                    </select>
                  @error('role_id')
                    <div class="invalid-feedback">
                      {{ $message }}
                    </div>
                  @enderror
                </div>

                

                {{-- Name --}}
                <div class="col-md-4 mb-3">
                  <label for="name" class="form-label">Name</label>
                  <input required wire:model="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{old('name')}}">
                  @error('name')
                  <div class="invalid-feedback">
                    {{ $message }}
                  </div>
                @enderror
                </div>

                {{-- Email --}}
                <div class="col-md-4 mb-3">
                  <label for="email" class="form-label">Email</label>
                  <input required wire:model="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{old('email')}}">
                  @error('email')
                    <div class="invalid-feedback">
                      {{ $message }}
                    </div>
                  @enderror
                </div>

                {{-- Status --}}
                <div class="col-md-4 mb-3">
                  <label for="academic_year" class="form-label">Status</label>
                  <select required wire:model="active" name="active" class="form-control @error('active') is-invalid @enderror" value="{{old('active')}}">
                    <option value="1">Active</option>
                    <option value="0">Banned</option>
                    </select>
                  @error('status')
                    <div class="invalid-feedback">
                      {{ $message }}
                    </div>
                  @enderror
                </div>
                </div>
              </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="bi-icons bi-save"></i> {{"Save Changes"}}
                </button>
                <a href=" #" wire:click="cancel" class="btn btn-danger">
                  <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
          
        </form>
    </div>

    @else
    {{-- Table --}}
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">All Users</h3>
        <div class="card-tools">

          {{-- Search form --}}
          <form class="d-inline-block me-2">
              <div class="input-group input-group-sm">
                  {{--  show inline error messages --}}
                  <input wire:model.live.debounce.700ms="search" type="text" name="search"
                    class="form-control {{ $errors->has('search') ? 'is-invalid' : '' }}"
                    placeholder="Search Base Bookings" 
                    autofocus>
                    @error('search')
                      <div class="invalid-feedback">
                        {{ $message }}
                      </div>
                    @enderror
              </div>
          </form>
          
          {{-- Link to reset --}}
          <a href="#"  wire:click="clearSearch"
            class="btn btn-success" 
            title="Reset">
            <i class="bi bi-arrow-clockwise"></i>
          </a>
        </div>
      </div>      
      <!-- /.card-header -->
      
      <div class="card-body">
        @if(count($users)!=0)
          <table class="table table-bordered table-striped">
            <thead>
              <tr>
                <th style="width: 10px">Number</th>
                <th>
                    <a href="#" wire:click="orderBy('user_name')">
                        User Name
                    </a>
                    @if($orderDirection1=="asc")
                        <i class="bi bi-sort-alpha-up"></i>
                    @else
                        <i class="bi bi-sort-alpha-down"></i>
                    @endif
                </th>
                <th>
                    <a href="#" wire:click="orderBy('role_name')">
                        Role Name
                    </a>
                    @if($orderDirection2=="asc")
                        <i class="bi bi-sort-alpha-up"></i>
                    @else
                        <i class="bi bi-sort-alpha-down"></i>
                    @endif
                </th>
                <th>Admission Number / Employee ID</th>
                <th>Faculty and Course</th>
                <th>User Email</th>
                <th>User Status</th>
                <th>Actions</th>
              </tr>
            </thead>        
            <tbody>
              @foreach($users as $user)
              <tr>
                {{-- Can have {{ $loop->iteration }} --}}
                <td>{{$loop->iteration}}</td>
                <td>{{ trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: $user->name }}</td>
                <td>{{$user->role->role_name}}</td>
                <td>{{ $user->admission_number ?: ($user->employee_id ?: '-') }}</td>
                <td>
                  @if($user->faculty || $user->course)
                    {{ $user->faculty ?: '-' }} / {{ $user->course ?: '-' }}
                  @else
                    -
                  @endif
                </td>
                <td>{{$user->email}}</td>
                <td><span class="{{ $user->active==1 ? 'badge bg-success' : 'badge bg-warning text-dark' }}">
                    {{$user->active ? "Active" : "Banned"}}
                </span>
                </td>
                <td>
                  <div class="btn-group" role="group">
                    <a href="#" wire:click="regulate({{$user->id}})"
                      class="{{$user->active==1? 'btn btn-info btn-sm' : 'btn btn-success btn-sm'}}" 
                      title="Ban/Activate">
                        @if($user->active==1)
                            <i class="bi bi-ban"></i>
                        @else
                            <i class="bi bi-hand-thumbs-up-fill"></i>
                        @endif
                    </a>
                    <a href="#" wire:click="edit({{$user->id}})"
                      class="btn btn-warning btn-sm" 
                      title="Edit">
                      <i class="bi bi-pencil"></i>
                    </a>
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        @else
          <tbody>
            <p class="text-danger">No User, Role or Email matches the search key</p>
          </tbody>
        @endif
      </div>
      <!-- /.card-body -->
      <div class="card-footer">
        {{-- Pagination --}}
          <div class="mt-3">
                {{ $users->links('pagination::bootstrap-5') }}
          </div>
      </div>
    </div>

    @endif


</div>
