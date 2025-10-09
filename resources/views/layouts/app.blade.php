<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Inline CSS (ไม่ต้องคอมไพล์ก็ทำงานได้) -->
        <style>
          /* กลุ่มกล่องโปรไฟล์: เว้นแต่ละใบเหมือน space-y-10 */
          .profile-stack > .profile-card + .profile-card { margin-top: 2.5rem; } /* 40px */

          /* กล่องแต่ละใบ: มุมโค้ง + padding พอดี */
          .profile-card { border-radius: 1rem; padding: 2rem; background: #ffffff; }
          .dark .profile-card { background-color: #1f2937; } /* dark:bg-gray-800 */

          /* การ์ดแรก: หัวข้ออย่าชิดบนเกินไป */
          .profile-stack > .profile-card:first-child { padding-top: 2.5rem; }  /* pt-10 */

          /* การ์ดสุดท้าย: ชิดขอบล่างมากขึ้น แต่อย่าให้ปุ่มติดจนเกินไป */
          .profile-stack > .profile-card:last-child { padding-bottom: 1rem; }  /* pb-4 */

          /* บล็อกปุ่ม */
          .card-actions { margin-top: 1rem; display: flex; justify-content: flex-start; gap: .75rem; }
          .profile-stack > .profile-card:last-child .card-actions { margin-bottom: .5rem; }

          /* ให้ main มี padding แนวนอนนิดหน่อยเวลาไม่ได้ใส่ container เอง */
          main .__page-container { max-width: 80rem; margin: 0 auto; padding-left: 1.5rem; padding-right: 1.5rem; }
          @media (min-width: 1024px) {
            main .__page-container { padding-left: 2rem; padding-right: 2rem; }
          }
        </style>

        <!-- Scripts (ใช้หรือไม่ใช้ Vite ก็ได้ ถ้าไม่ใช้ปล่อยไว้ก็ไม่เป็นไร) -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @stack('styles') {{-- เผื่อหน้าไหนอยาก inject CSS เพิ่ม --}}
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                <div class="__page-container">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </body>
</html>
