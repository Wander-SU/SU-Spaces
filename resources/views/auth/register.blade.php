<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register | SU-Spaces</title>
    <link rel="icon" type="image/png" href="{{ asset('images/strathmore_emblem.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        };
    </script>
</head>
<body class="min-h-screen bg-[#FDFDFC] font-['Instrument_Sans',sans-serif] dark:bg-[#0a0a0a]">
    {{-- Full-page background image for the registration screen --}}
    <div class="relative min-h-screen overflow-hidden bg-center bg-cover" style="background-image: url('{{ asset('images/sign_up_background.jpeg') }}');">
        {{-- Frosted overlay to keep text readable on top of the image --}}
        <div class="absolute inset-0 bg-white/35 dark:bg-black/55 backdrop-blur-[3px]"></div>

        {{-- Desktop-only floating logo --}}
        <div class="pointer-events-none absolute left-4 top-4 z-20 hidden md:block md:left-6 md:top-6">
            <img
                src="{{ asset('images/strathmore_logo.png') }}"
                alt="Strathmore University Logo"
                class="h-28 w-auto bg-transparent p-0 drop-shadow-md lg:h-48"
            >
        </div>

        {{-- Main centered registration card --}}
        <div class="relative z-10 mx-auto flex min-h-screen w-full max-w-6xl items-center justify-center p-3 sm:p-6">
            <div class="w-full max-w-5xl rounded-xl border border-white/45 bg-white/30 p-4 shadow-sm backdrop-blur-2xl dark:border-white/15 dark:bg-[#111110]/40 sm:rounded-2xl sm:p-8">
                {{-- Mobile-only compact logo inside the card --}}
                <div class="mb-4 flex justify-start md:hidden">
                    <img
                        src="{{ asset('images/strathmore_logo.png') }}"
                        alt="Strathmore University Logo"
                        class="h-12 w-auto bg-transparent p-0"
                    >
                </div>
                <div class="mb-7 text-center sm:mb-8">
                    <h1 class="text-xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC] sm:text-3xl">Create Your SU-Spaces Account</h1>
                    <p class="mt-2 text-base text-[#57534e] dark:text-[#b8b8b5]">Complete all required fields to proceed.</p>
                </div>

                {{-- Global error banner shown when backend validation fails --}}
                @if ($errors->any())
                    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300" role="alert">
                        <strong>Please fix the highlighted fields.</strong>
                    </div>
                @endif

                {{-- Registration form posts to backend registration endpoint --}}
                <form action="{{ route('register') }}" method="POST" novalidate>
                    @csrf

                    {{-- Determines which academic fields are required (student vs lecturer) --}}
                    <div class="mb-6">
                        <label for="account_type" class="mb-2 block text-base font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Register As</label>
                        <select id="account_type" name="account_type" class="w-full rounded-lg border border-[#d7d7d3] bg-white px-3 py-3 text-base text-[#1b1b18] outline-none transition focus:border-[#1b1b18] focus:ring-2 focus:ring-[#1b1b18]/10 dark:border-[#3E3E3A] dark:bg-[#171716] dark:text-[#EDEDEC] dark:focus:border-[#EDEDEC] dark:focus:ring-[#EDEDEC]/10" required>
                            <option value="student" {{ old('account_type', 'student') === 'student' ? 'selected' : '' }}>Student</option>
                            <option value="lecturer" {{ old('account_type') === 'lecturer' ? 'selected' : '' }}>Lecturer</option>
                        </select>
                        <x-input-error :messages="$errors->get('account_type')" class="mt-2" />
                    </div>

                    {{-- Personal details section shared by all users --}}
                    <section class="mb-6 rounded-xl border border-white/50 bg-white/20 p-4 backdrop-blur-lg dark:border-white/15 dark:bg-[#141413]/36 sm:p-5">
                        <h2 class="mb-4 text-center text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Personal Information</h2>
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div>
                                <label for="first_name" class="mb-2 block text-base font-medium text-[#1b1b18] dark:text-[#EDEDEC]">First Name</label>
                                <input id="first_name" type="text" name="first_name" value="{{ old('first_name') }}" class="w-full rounded-lg border border-white/55 bg-white/58 px-3 py-3 text-base text-[#1b1b18] outline-none transition focus:border-[#1b1b18] focus:ring-2 focus:ring-[#1b1b18]/15 dark:border-white/20 dark:bg-[#171716]/70 dark:text-[#EDEDEC] dark:focus:border-[#EDEDEC] dark:focus:ring-[#EDEDEC]/15" required autofocus>
                                <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                            </div>
                            <div>
                                <label for="last_name" class="mb-2 block text-base font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Last Name</label>
                                <input id="last_name" type="text" name="last_name" value="{{ old('last_name') }}" class="w-full rounded-lg border border-white/55 bg-white/58 px-3 py-3 text-base text-[#1b1b18] outline-none transition focus:border-[#1b1b18] focus:ring-2 focus:ring-[#1b1b18]/15 dark:border-white/20 dark:bg-[#171716]/70 dark:text-[#EDEDEC] dark:focus:border-[#EDEDEC] dark:focus:ring-[#EDEDEC]/15" required>
                                <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                            </div>

                            <div>
                                <label for="email" class="mb-2 block text-base font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Email Address</label>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" class="w-full rounded-lg border border-white/55 bg-white/58 px-3 py-3 text-base text-[#1b1b18] outline-none transition focus:border-[#1b1b18] focus:ring-2 focus:ring-[#1b1b18]/15 dark:border-white/20 dark:bg-[#171716]/70 dark:text-[#EDEDEC] dark:focus:border-[#EDEDEC] dark:focus:ring-[#EDEDEC]/15" required>
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>
                            <div>
                                <label for="gender" class="mb-2 block text-base font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Gender</label>
                                <select id="gender" name="gender" class="w-full rounded-lg border border-white/55 bg-white/58 px-3 py-3 text-base text-[#1b1b18] outline-none transition focus:border-[#1b1b18] focus:ring-2 focus:ring-[#1b1b18]/15 dark:border-white/20 dark:bg-[#171716]/70 dark:text-[#EDEDEC] dark:focus:border-[#EDEDEC] dark:focus:ring-[#EDEDEC]/15" required>
                                    <option value="" selected disabled>Select gender</option>
                                    <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                                </select>
                                <x-input-error :messages="$errors->get('gender')" class="mt-2" />
                            </div>

                            <div>
                                <label for="password" class="mb-2 block text-base font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Password</label>
                                <input id="password" type="password" name="password" class="w-full rounded-lg border border-white/55 bg-white/58 px-3 py-3 text-base text-[#1b1b18] outline-none transition focus:border-[#1b1b18] focus:ring-2 focus:ring-[#1b1b18]/15 dark:border-white/20 dark:bg-[#171716]/70 dark:text-[#EDEDEC] dark:focus:border-[#EDEDEC] dark:focus:ring-[#EDEDEC]/15" required>
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>
                            <div>
                                <label for="password_confirmation" class="mb-2 block text-base font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Password Confirmation</label>
                                <input id="password_confirmation" type="password" name="password_confirmation" class="w-full rounded-lg border border-white/55 bg-white/58 px-3 py-3 text-base text-[#1b1b18] outline-none transition focus:border-[#1b1b18] focus:ring-2 focus:ring-[#1b1b18]/15 dark:border-white/20 dark:bg-[#171716]/70 dark:text-[#EDEDEC] dark:focus:border-[#EDEDEC] dark:focus:ring-[#EDEDEC]/15" required>
                                {{-- Client-side password mismatch helper text --}}
                                <p id="passwordMatchMessage" class="mt-2 hidden text-sm text-red-600 dark:text-red-400">Passwords do not match.</p>
                            </div>
                        </div>
                    </section>

                    {{-- Academic section changes dynamically based on selected account type --}}
                    <section class="mb-6 rounded-xl border border-white/50 bg-white/20 p-4 backdrop-blur-lg dark:border-white/15 dark:bg-[#141413]/36 sm:p-5">
                        <h2 class="mb-4 text-center text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Academic Information</h2>
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div id="admissionWrap">
                                <label for="admission_number" class="mb-2 block text-base font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Admission Number</label>
                                <input id="admission_number" type="text" name="admission_number" value="{{ old('admission_number') }}" inputmode="numeric" pattern="\d{6}" maxlength="6" class="w-full rounded-lg border border-white/55 bg-white/58 px-3 py-3 text-base text-[#1b1b18] outline-none transition focus:border-[#1b1b18] focus:ring-2 focus:ring-[#1b1b18]/15 dark:border-white/20 dark:bg-[#171716]/70 dark:text-[#EDEDEC] dark:focus:border-[#EDEDEC] dark:focus:ring-[#EDEDEC]/15">
                                <x-input-error :messages="$errors->get('admission_number')" class="mt-2" />
                            </div>
                            <div id="employeeWrap" class="hidden">
                                <label for="employee_id" class="mb-2 block text-base font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Employee ID</label>
                                <input id="employee_id" type="text" name="employee_id" value="{{ old('employee_id') }}" inputmode="numeric" pattern="\d{5,6}" maxlength="6" class="w-full rounded-lg border border-white/55 bg-white/58 px-3 py-3 text-base text-[#1b1b18] outline-none transition focus:border-[#1b1b18] focus:ring-2 focus:ring-[#1b1b18]/15 dark:border-white/20 dark:bg-[#171716]/70 dark:text-[#EDEDEC] dark:focus:border-[#EDEDEC] dark:focus:ring-[#EDEDEC]/15">
                                <x-input-error :messages="$errors->get('employee_id')" class="mt-2" />
                            </div>

                            <div>
                                <label for="faculty" class="mb-2 block text-base font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Faculty</label>
                                <select id="faculty" name="faculty" class="w-full rounded-lg border border-white/55 bg-white/58 px-3 py-3 text-base text-[#1b1b18] outline-none transition focus:border-[#1b1b18] focus:ring-2 focus:ring-[#1b1b18]/15 dark:border-white/20 dark:bg-[#171716]/70 dark:text-[#EDEDEC] dark:focus:border-[#EDEDEC] dark:focus:ring-[#EDEDEC]/15" required>
                                    <option value="" selected disabled>Select faculty</option>
                                    @foreach (['SCES', 'SIMS', 'SLS', 'SBS', 'STH', 'SHSS', 'SI'] as $faculty)
                                        <option value="{{ $faculty }}" {{ old('faculty') === $faculty ? 'selected' : '' }}>{{ $faculty }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('faculty')" class="mt-2" />
                            </div>

                            <div>
                                <label for="username" class="mb-2 block text-base font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Username</label>
                                <input id="username" type="text" name="username" value="{{ old('username') }}" class="w-full rounded-lg border border-white/50 bg-white/48 px-3 py-3 text-base text-[#1b1b18] outline-none dark:border-white/20 dark:bg-[#1c1c1a]/70 dark:text-[#EDEDEC]" readonly required>
                                {{-- This field is auto-generated from admission number or employee ID --}}
                                <p class="mt-2 text-xs text-[#57534e] dark:text-[#b8b8b5]">Auto-generated from Admission Number or Employee ID.</p>
                                <x-input-error :messages="$errors->get('username')" class="mt-2" />
                            </div>

                            <div id="yearWrap">
                                <label for="year_of_study" class="mb-2 block text-base font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Year of Study</label>
                                <select id="year_of_study" name="year_of_study" class="w-full rounded-lg border border-white/55 bg-white/58 px-3 py-3 text-base text-[#1b1b18] outline-none transition focus:border-[#1b1b18] focus:ring-2 focus:ring-[#1b1b18]/15 dark:border-white/20 dark:bg-[#171716]/70 dark:text-[#EDEDEC] dark:focus:border-[#EDEDEC] dark:focus:ring-[#EDEDEC]/15">
                                    <option value="" selected disabled>Select year</option>
                                    @foreach (['1', '2', '3', '4', '5'] as $year)
                                        <option value="{{ $year }}" {{ old('year_of_study') === $year ? 'selected' : '' }}>{{ $year }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('year_of_study')" class="mt-2" />
                            </div>

                            <div id="officeWrap" class="hidden">
                                <label for="office_location" class="mb-2 block text-base font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Office Location</label>
                                <input id="office_location" type="text" name="office_location" value="{{ old('office_location') }}" class="w-full rounded-lg border border-white/55 bg-white/58 px-3 py-3 text-base text-[#1b1b18] outline-none transition focus:border-[#1b1b18] focus:ring-2 focus:ring-[#1b1b18]/15 dark:border-white/20 dark:bg-[#171716]/70 dark:text-[#EDEDEC] dark:focus:border-[#EDEDEC] dark:focus:ring-[#EDEDEC]/15">
                                <x-input-error :messages="$errors->get('office_location')" class="mt-2" />
                            </div>

                            <div>
                                <label for="course" class="mb-2 block text-base font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Course</label>
                                <select id="course" name="course" class="w-full rounded-lg border border-white/55 bg-white/58 px-3 py-3 text-base text-[#1b1b18] outline-none transition focus:border-[#1b1b18] focus:ring-2 focus:ring-[#1b1b18]/15 disabled:bg-white/35 dark:border-white/20 dark:bg-[#171716]/70 dark:text-[#EDEDEC] dark:disabled:bg-[#1c1c1a]/45 dark:focus:border-[#EDEDEC] dark:focus:ring-[#EDEDEC]/15" disabled>
                                    <option value="">Select faculty first</option>
                                </select>
                                {{-- Helper text updates based on selected account type --}}
                                <p id="courseHelp" class="mt-2 text-xs text-[#57534e] dark:text-[#b8b8b5]">Fill faculty to load course options.</p>
                                <x-input-error :messages="$errors->get('course')" class="mt-2" />
                            </div>
                        </div>
                    </section>

                    {{-- Verify-email equivalent: user requests a token, then pastes it here before final submit. --}}
                    <section class="mb-6 rounded-xl border border-white/50 bg-white/20 p-4 backdrop-blur-lg dark:border-white/15 dark:bg-[#141413]/36 sm:p-5">
                        <h2 class="mb-4 text-center text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Email Verification Token</h2>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:items-end">
                            <div>
                                {{-- This value is checked server-side against the cached token for this email. --}}
                                <label for="registration_token" class="mb-2 block text-base font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Paste Token</label>
                                <input
                                    id="registration_token"
                                    type="text"
                                    name="registration_token"
                                    value="{{ old('registration_token') }}"
                                    maxlength="6"
                                    class="w-full rounded-lg border border-white/55 bg-white/58 px-3 py-3 text-base text-[#1b1b18] outline-none transition focus:border-[#1b1b18] focus:ring-2 focus:ring-[#1b1b18]/15 disabled:bg-white/35 dark:border-white/20 dark:bg-[#171716]/70 dark:text-[#EDEDEC] dark:disabled:bg-[#1c1c1a]/45 dark:focus:border-[#EDEDEC] dark:focus:ring-[#EDEDEC]/15"
                                    @disabled(!old('registration_token'))
                                    required
                                >
                                <x-input-error :messages="$errors->get('registration_token')" class="mt-2" />
                            </div>

                            <div>
                                {{-- Stays disabled until required fields are valid; JS enables and triggers send-token API call. --}}
                                <button
                                    id="sendTokenBtn"
                                    type="button"
                                    class="w-full rounded-lg border border-[#1b1b18]/20 bg-white/70 px-4 py-3 text-base font-semibold text-[#1b1b18] transition hover:bg-white disabled:cursor-not-allowed disabled:opacity-50 dark:border-white/20 dark:bg-[#171716]/70 dark:text-[#EDEDEC] dark:hover:bg-[#1f1f1d]"
                                    disabled
                                >
                                    Send Token
                                </button>
                            </div>
                        </div>

                        <p id="tokenStatusMessage" class="mt-3 text-sm text-[#57534e] dark:text-[#b8b8b5]"></p>
                    </section>

                    <button type="submit" class="w-full rounded-lg bg-gradient-to-r from-[#F11D22] to-[#FFCC00] px-4 py-3.5 text-base font-semibold text-[#1b1b18] transition hover:brightness-95 focus:outline-none focus:ring-2 focus:ring-[#F11D22]/30">
                        Register
                    </button>

                    <p class="mt-4 text-center text-base text-[#57534e] dark:text-[#b8b8b5]">
                        Already have an account?
                        <a href="{{ url('/login') }}" class="font-semibold text-[#1b1b18] hover:text-black dark:text-[#EDEDEC] dark:hover:text-white">Login</a>
                    </p>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Cache frequently-used form elements so dynamic UI updates are fast and readable.
        const accountType = document.getElementById('account_type');
        const admissionWrap = document.getElementById('admissionWrap');
        const employeeWrap = document.getElementById('employeeWrap');
        const yearWrap = document.getElementById('yearWrap');
        const officeWrap = document.getElementById('officeWrap');
        const admissionInput = document.getElementById('admission_number');
        const employeeInput = document.getElementById('employee_id');
        const usernameInput = document.getElementById('username');
        const yearSelect = document.getElementById('year_of_study');
        const officeInput = document.getElementById('office_location');
        const facultySelect = document.getElementById('faculty');
        const courseSelect = document.getElementById('course');
        const courseHelp = document.getElementById('courseHelp');
        const passwordInput = document.getElementById('password');
        const passwordConfirmationInput = document.getElementById('password_confirmation');
        const passwordMatchMessage = document.getElementById('passwordMatchMessage');
        const registrationTokenInput = document.getElementById('registration_token');
        const sendTokenBtn = document.getElementById('sendTokenBtn');
        const tokenStatusMessage = document.getElementById('tokenStatusMessage');

        // Faculty-to-course lookup used to populate the course dropdown.
        const coursesByFaculty = {
            SCES: ['BSc Computer Science', 'BSc Software Engineering', 'BSc Data Science'],
            SIMS: ['BSc Information Systems', 'BSc Information Technology'],
            SLS: ['Bachelor of Laws'],
            SBS: ['BBA Accounting', 'BBA Finance', 'BBA Marketing'],
            STH: ['BA Theology', 'BA Chaplaincy'],
            SHSS: ['BA Sociology', 'BA Counseling Psychology'],
            SI: ['Certificate in Innovation', 'Diploma in Entrepreneurship']
        };

        const oldCourse = @json(old('course'));

        // Keep username format consistent by stripping spaces and lowercasing.
        function normalizeUsername(value) {
            return (value || '').trim().replace(/\s+/g, '').toLowerCase();
        }

        function clearTokenStatus() {
            tokenStatusMessage.textContent = '';
            tokenStatusMessage.classList.remove('text-emerald-600', 'dark:text-emerald-400', 'text-red-600', 'dark:text-red-400');
        }

        // Auto-generate username from the active identity field.
        function updateUsername() {
            const source = accountType.value === 'lecturer' ? employeeInput.value : admissionInput.value;
            usernameInput.value = normalizeUsername(source);
        }

        // Load valid courses for selected faculty and account type.
        function renderCourses() {
            const faculty = facultySelect.value;
            const isLecturer = accountType.value === 'lecturer';
            const courses = coursesByFaculty[faculty] || [];

            courseSelect.innerHTML = '';
            if (!faculty) {
                courseSelect.disabled = true;
                courseSelect.append(new Option('Select faculty first', ''));
                courseHelp.textContent = 'Fill faculty to load course options.';
                return;
            }

            courseSelect.disabled = false;
            courseSelect.append(new Option(isLecturer ? 'Select course (optional)' : 'Select course', ''));
            courses.forEach((course) => courseSelect.append(new Option(course, course)));

            if (oldCourse && courses.includes(oldCourse)) {
                courseSelect.value = oldCourse;
            }

            courseHelp.textContent = isLecturer
                ? 'Course is optional for lecturers.'
                : 'Course is required for students.';
            courseSelect.required = !isLecturer;
        }

        // Toggle student/lecturer specific fields and required states.
        function updateMode() {
            const isLecturer = accountType.value === 'lecturer';

            admissionWrap.classList.toggle('hidden', isLecturer);
            yearWrap.classList.toggle('hidden', isLecturer);
            employeeWrap.classList.toggle('hidden', !isLecturer);
            officeWrap.classList.toggle('hidden', !isLecturer);

            admissionInput.required = !isLecturer;
            yearSelect.required = !isLecturer;
            employeeInput.required = isLecturer;
            officeInput.required = isLecturer;

            renderCourses();
            updateUsername();
        }

        // Provide immediate client-side password mismatch feedback.
        function validatePasswordMatch() {
            const passwordValue = passwordInput.value;
            const confirmationValue = passwordConfirmationInput.value;

            if (confirmationValue.length === 0) {
                passwordConfirmationInput.setCustomValidity('');
                passwordMatchMessage.classList.add('hidden');
                return;
            }

            if (passwordValue !== confirmationValue) {
                passwordConfirmationInput.setCustomValidity('Passwords do not match.');
                passwordMatchMessage.classList.remove('hidden');
                return;
            }

            passwordConfirmationInput.setCustomValidity('');
            passwordMatchMessage.classList.add('hidden');
        }

        function areBaseFieldsFilled() {
            const account = accountType.value;
            const commonFieldsFilled = [
                document.getElementById('first_name').value.trim(),
                document.getElementById('last_name').value.trim(),
                document.getElementById('email').value.trim(),
                document.getElementById('gender').value,
                passwordInput.value,
                passwordConfirmationInput.value,
                document.getElementById('faculty').value,
            ].every(Boolean);

            if (!commonFieldsFilled) {
                return false;
            }

            if (passwordInput.value !== passwordConfirmationInput.value) {
                return false;
            }

            if (account === 'student') {
                return Boolean(
                    admissionInput.value.trim() &&
                    yearSelect.value &&
                    courseSelect.value
                );
            }

            return Boolean(
                employeeInput.value.trim() &&
                officeInput.value.trim()
            );
        }

        function updateSendTokenButtonState() {
            sendTokenBtn.disabled = !areBaseFieldsFilled();
        }

        async function sendRegistrationToken() {
            if (sendTokenBtn.disabled) {
                return;
            }

            clearTokenStatus();
            sendTokenBtn.disabled = true;
            sendTokenBtn.textContent = 'Sending...';

            const payload = {
                account_type: accountType.value,
                first_name: document.getElementById('first_name').value.trim(),
                last_name: document.getElementById('last_name').value.trim(),
                gender: document.getElementById('gender').value,
                email: document.getElementById('email').value.trim(),
                password: passwordInput.value,
                password_confirmation: passwordConfirmationInput.value,
                faculty: document.getElementById('faculty').value,
                course: courseSelect.value,
                admission_number: admissionInput.value.trim(),
                year_of_study: yearSelect.value,
                employee_id: employeeInput.value.trim(),
                office_location: officeInput.value.trim(),
            };

            try {
                const response = await fetch("{{ route('register.send-token') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(payload),
                });

                const data = await response.json();

                if (!response.ok) {
                    const message = data.message || Object.values(data.errors || {}).flat()[0] || 'Unable to send token. Please try again.';
                    tokenStatusMessage.textContent = message;
                    tokenStatusMessage.classList.add('text-red-600', 'dark:text-red-400');
                    return;
                }

                registrationTokenInput.disabled = false;
                registrationTokenInput.focus();
                tokenStatusMessage.textContent = data.message || 'Token sent. Check your email.';
                tokenStatusMessage.classList.add('text-emerald-600', 'dark:text-emerald-400');
            } catch (error) {
                tokenStatusMessage.textContent = 'Network error while sending token. Please try again.';
                tokenStatusMessage.classList.add('text-red-600', 'dark:text-red-400');
            } finally {
                sendTokenBtn.textContent = 'Send Token';
                updateSendTokenButtonState();
            }
        }

        // Wire events so UI updates as the user types/selects.
        accountType.addEventListener('change', updateMode);
        admissionInput.addEventListener('input', updateUsername);
        employeeInput.addEventListener('input', updateUsername);
        facultySelect.addEventListener('change', renderCourses);
        passwordInput.addEventListener('input', validatePasswordMatch);
        passwordConfirmationInput.addEventListener('input', validatePasswordMatch);
        [
            document.getElementById('first_name'),
            document.getElementById('last_name'),
            document.getElementById('email'),
            document.getElementById('gender'),
            accountType,
            admissionInput,
            yearSelect,
            employeeInput,
            officeInput,
            courseSelect,
            facultySelect,
            passwordInput,
            passwordConfirmationInput,
        ].forEach((element) => {
            element.addEventListener('input', updateSendTokenButtonState);
            element.addEventListener('change', updateSendTokenButtonState);
        });
        sendTokenBtn.addEventListener('click', sendRegistrationToken);

        // Initialize dynamic form state on first load.
        updateMode();
        renderCourses();
        validatePasswordMatch();
        updateSendTokenButtonState();
    </script>
</body>
</html>
