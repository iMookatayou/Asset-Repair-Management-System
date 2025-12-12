{{-- resources/views/maintenance/rating/form.blade.php --}}
@extends('layouts.app')

@section('title', 'ให้คะแนนงานซ่อม #' . $req->id)

@section('content')
    <div class="max-w-xl mx-auto py-8">
        {{-- Breadcrumb / หัวข้อ --}}
        <div class="mb-6">
            <h1 class="text-xl font-semibold mb-1">
                ให้คะแนนงานซ่อม #{{ $req->id }}
            </h1>
            <p class="text-sm text-gray-600">
                กรุณาให้คะแนนความพึงพอใจต่อการดำเนินงานและระบุความคิดเห็นเพิ่มเติมหากมี
            </p>
        </div>

        {{-- แสดง error รวม --}}
        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                <div class="font-semibold mb-1">ไม่สามารถบันทึกข้อมูลได้</div>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- กล่องฟอร์ม --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <form method="POST"
                  action="{{ route('maintenance.rating.store', $req) }}">
                @csrf

                {{-- แสดงข้อมูลงานสั้น ๆ --}}
                <div class="mb-6 border-b border-gray-100 pb-4">
                    <div class="text-sm text-gray-500 mb-1">
                        งานซ่อมหมายเลข: <span class="font-medium text-gray-800">#{{ $req->id }}</span>
                    </div>

                    @if (! empty($req->title))
                        <div class="text-sm text-gray-700">
                            หัวข้อ: <span class="font-medium">{{ $req->title }}</span>
                        </div>
                    @endif

                    @if (! empty($req->location))
                        <div class="text-sm text-gray-700">
                            สถานที่: <span class="font-medium">{{ $req->location }}</span>
                        </div>
                    @endif

                    @if (! empty($req->technician) && ! empty($req->technician->name))
                        <div class="mt-2 text-sm text-gray-700">
                            ช่างผู้ดูแล: <span class="font-medium">{{ $req->technician->name }}</span>
                        </div>
                    @endif
                </div>

                {{-- คะแนน --}}
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        คะแนนความพึงพอใจ <span class="text-red-500">*</span>
                    </label>

                    <p class="text-xs text-gray-500 mb-2">
                        เลือกคะแนน 1–5 ดาว (5 = พึงพอใจมาก, 1 = ไม่พึงพอใจ)
                    </p>

                    <div class="flex flex-row-reverse justify-end gap-2">
                        @for ($i = 5; $i >= 1; $i--)
                            <label class="flex flex-col items-center cursor-pointer">
                                <input
                                    type="radio"
                                    name="score"
                                    value="{{ $i }}"
                                    class="sr-only"
                                    @checked(old('score') == $i)
                                >
                                <div class="text-2xl">
                                    {{-- ถ้าอยากทำไอคอนดาวจริง ๆ ค่อยเอา icon มาแทน --}}
                                    {{ $i }}
                                </div>
                                <div class="text-[11px] text-gray-500 mt-1">
                                    ดาว
                                </div>
                            </label>
                        @endfor
                    </div>

                    @error('score')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- ความคิดเห็น --}}
                <div class="mb-6">
                    <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">
                        ความคิดเห็นเพิ่มเติม
                    </label>

                    <textarea
                        id="comment"
                        name="comment"
                        rows="4"
                        class="block w-full rounded-lg border @error('comment') border-red-300 @else border-gray-300 @enderror
                               text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="คุณสามารถระบุปัญหา ข้อเสนอแนะ หรือคำติชมอื่น ๆ ได้ที่นี่..."
                    >{{ old('comment') }}</textarea>

                    <p class="mt-1 text-xs text-gray-500">
                        * ถ้าให้ 1–2 ดาว ระบบจะบังคับให้กรอกความคิดเห็น
                    </p>

                    @error('comment')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- ปุ่มกด --}}
                <div class="flex items-center justify-between gap-3">
                    <a href="{{ route('maintenance.show', $req) }}"
                       class="inline-flex items-center justify-center px-4 py-2 rounded-lg border border-gray-300
                              text-sm font-medium text-gray-700 hover:bg-gray-50">
                        ย้อนกลับไปหน้ารายละเอียดงาน
                    </a>

                    <button type="submit"
                            class="inline-flex items-center justify-center px-4 py-2 rounded-lg
                                   bg-blue-600 text-white text-sm font-medium hover:bg-blue-700
                                   focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500">
                        บันทึกคะแนน
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
