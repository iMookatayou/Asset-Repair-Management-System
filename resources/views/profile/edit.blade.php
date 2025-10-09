<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- ใช้ gap แทน space-y เพื่อกัน margin ซ้อนกัน --}}
            <div class="flex flex-col gap-8">

                {{-- การ์ดแรก: หัวข้อไม่ชิดบน --}}
                <section class="bg-white dark:bg-gray-800 shadow sm:rounded-2xl pt-10 sm:pt-12 pb-8 px-6 sm:px-8">
                    @include('profile.partials.update-profile-information-form')
                </section>

                {{-- การ์ดกลาง: spacing มาตรฐาน --}}
                <section class="bg-white dark:bg-gray-800 shadow sm:rounded-2xl p-6 sm:p-8">
                    @include('profile.partials.update-password-form')
                </section>

                {{-- การ์ดสุดท้าย: ชิดขอบล่างมากขึ้น แต่ปุ่มเว้นนิดนึง --}}
                <section class="bg-white dark:bg-gray-800 shadow sm:rounded-2xl pt-8 pb-3 px-6 sm:px-8">
                    @include('profile.partials.delete-user-form')
                </section>

            </div>
        </div>
    </div>
</x-app-layout>
