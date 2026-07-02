<!doctype html>
<html lang="en">
<head>
    {{-- Basic document metadata + shared typography/font setup --}}
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Welcome | SU-Spaces</title>
    <link rel="icon" type="image/png" href="{{ asset('images/strathmore_emblem.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#FDFDFC] font-['Inter',sans-serif]">
    {{-- Full-screen hero wrapper with the landing background image --}}
    <div class="relative min-h-screen overflow-hidden bg-center bg-cover" style="background-image: url('{{ asset('images/landing_page_background.png') }}');">
        {{-- Grey transparent overlay to mute the background while keeping it visible --}}
        <div class="absolute inset-0 bg-gray-500/35"></div>

        {{-- University logo pinned to the top-left corner --}}
        <div class="absolute left-4 top-4 z-20 md:left-6 md:top-6">
            <img
                src="{{ asset('images/strathmore_logo.png') }}"
                alt="Strathmore University Logo"
                class="h-32 w-auto bg-transparent md:h-48 lg:h-56"
            >
        </div>

        {{-- Main centered content area (frosted glass card + call-to-actions) --}}
        <main class="relative z-10 mx-auto flex min-h-screen w-full max-w-7xl items-center justify-center px-4 py-10 sm:px-6">
            {{-- Frosted glass panel that holds headline and auth entry buttons --}}
            <section class="w-full max-w-4xl rounded-2xl border border-white/50 bg-white/38 p-12 text-center shadow-lg backdrop-blur-md sm:p-20">
                <h1 class="text-3xl font-semibold leading-tight text-[#1b1b18] sm:text-4xl">Welcome to SU-Spaces. Let's find you a room</h1>

                {{-- Responsive button group: stacked on mobile, inline on larger screens --}}
                <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <a
                        href="{{ route('register') }}"
                        class="inline-flex w-full items-center justify-center rounded-lg border border-[#1b1b18]/20 bg-[#d4d4d4]/90 px-6 py-3 text-base font-semibold text-[#1b1b18] transition hover:bg-gradient-to-r hover:from-[#F11D22] hover:to-[#FFCC00] sm:w-auto"
                    >
                        Sign Up
                    </a>

                    <a
                        href="{{ route('login') }}"
                        class="inline-flex w-full items-center justify-center rounded-lg border border-[#1b1b18]/20 bg-[#d4d4d4]/90 px-6 py-3 text-base font-semibold text-[#1b1b18] transition hover:bg-gradient-to-r hover:from-[#F11D22] hover:to-[#FFCC00] sm:w-auto"
                    >
                        Log In
                    </a>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
