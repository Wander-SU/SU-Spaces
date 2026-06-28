<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset Password | SU-Spaces</title>
    <link rel="icon" type="image/png" href="{{ asset('images/strathmore_emblem.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = { darkMode: 'class' };
        </script>
    @endif
</head>
<body class="min-h-screen bg-[#FDFDFC] font-['Instrument_Sans',sans-serif] dark:bg-[#0a0a0a]">
    <div class="relative min-h-screen overflow-hidden bg-center bg-no-repeat" style="background-image: url('{{ asset('images/log_in_background.jpeg') }}'); background-size: 120vmin;">
        <div class="absolute inset-0 bg-white/35 dark:bg-black/55 backdrop-blur-[3px]"></div>

        <div class="pointer-events-none absolute left-4 top-4 z-20 hidden md:block md:left-6 md:top-6">
            <img src="{{ asset('images/strathmore_logo.png') }}" alt="Strathmore University Logo" class="h-28 w-auto rounded-md border border-white/50 bg-white/55 p-1 shadow-sm backdrop-blur-md dark:border-white/20 dark:bg-black/35 lg:h-48">
        </div>

        <div class="relative z-10 mx-auto flex min-h-screen w-full max-w-6xl items-center justify-center p-4 sm:p-6">
            <div class="w-full max-w-xl rounded-2xl border border-white/45 bg-white/30 p-7 shadow-sm backdrop-blur-2xl dark:border-white/15 dark:bg-[#111110]/40 sm:p-10">
                <div class="mb-4 flex justify-start md:hidden">
                    <img src="{{ asset('images/strathmore_logo.png') }}" alt="Strathmore University Logo" class="h-12 w-auto bg-transparent p-0">
                </div>

                <h1 class="text-center text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC] sm:text-3xl">Reset Password</h1>
                <p class="mt-2 text-center text-base text-[#57534e] dark:text-[#b8b8b5]">Set a new password for your account.</p>

                @if ($errors->any())
                    <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300" role="alert">
                        <strong>Please fix the highlighted fields.</strong>
                    </div>
                @endif

                {{-- Posts token + email + new password to NewPasswordController@store. --}}
                <form action="{{ route('password.store') }}" method="POST" class="mt-8 space-y-7" novalidate>
                    @csrf

                    {{-- Token comes from reset link and proves this reset request is authorized. --}}
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div>
                        <label for="email" class="mb-2 block text-base font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Email Address</label>
                        <input id="email" type="email" name="email" value="{{ old('email', $email ?? '') }}" class="w-full rounded-lg border border-white/55 bg-white/58 px-3 py-3 text-base text-[#1b1b18] outline-none transition focus:border-[#1b1b18] focus:ring-2 focus:ring-[#1b1b18]/15 dark:border-white/20 dark:bg-[#171716]/70 dark:text-[#EDEDEC] dark:focus:border-[#EDEDEC] dark:focus:ring-[#EDEDEC]/15" required autofocus>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-base font-medium text-[#1b1b18] dark:text-[#EDEDEC]">New Password</label>
                        <input id="password" type="password" name="password" class="w-full rounded-lg border border-white/55 bg-white/58 px-3 py-3 text-base text-[#1b1b18] outline-none transition focus:border-[#1b1b18] focus:ring-2 focus:ring-[#1b1b18]/15 dark:border-white/20 dark:bg-[#171716]/70 dark:text-[#EDEDEC] dark:focus:border-[#EDEDEC] dark:focus:ring-[#EDEDEC]/15" required>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div>
                        <label for="password_confirmation" class="mb-2 block text-base font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Confirm New Password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" class="w-full rounded-lg border border-white/55 bg-white/58 px-3 py-3 text-base text-[#1b1b18] outline-none transition focus:border-[#1b1b18] focus:ring-2 focus:ring-[#1b1b18]/15 dark:border-white/20 dark:bg-[#171716]/70 dark:text-[#EDEDEC] dark:focus:border-[#EDEDEC] dark:focus:ring-[#EDEDEC]/15" required>
                    </div>

                    <button type="submit" class="w-full rounded-lg bg-gradient-to-r from-[#F11D22] to-[#FFCC00] px-4 py-3.5 text-base font-semibold text-[#1b1b18] transition hover:brightness-95 focus:outline-none focus:ring-2 focus:ring-[#F11D22]/30">
                        Reset Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
