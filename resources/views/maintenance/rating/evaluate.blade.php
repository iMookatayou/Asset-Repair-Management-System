@extends('layouts.app')

@section('title', 'งานที่รอการให้คะแนน')

@section('content')
<div class="max-w-4xl mx-auto py-8">

    <h1 class="text-xl font-semibold mb-6">
        FEEDBACK / Evaluate
    </h1>

    {{-- งานที่รอการให้คะแนน --}}
    <div class="mb-8">
        <h2 class="text-lg font-semibold mb-3">
            งานที่รอการให้คะแนน
        </h2>

        @if ($pendingRequests->isEmpty())
            <div class="text-sm text-gray-500 bg-white border rounded-lg p-4">
                ตอนนี้ไม่มีงานที่รอการให้คะแนน
            </div>
        @else
            <div class="space-y-3">
                @foreach ($pendingRequests as $req)
                    <div class="bg-white border rounded-lg p-4 flex items-center justify-between">
                        <div>
                            <div class="text-sm text-gray-500">
                                งานซ่อม #{{ $req->id }}
                            </div>
                            <div class="font-semibold text-gray-800">
                                {{ $req->title ?? 'ไม่ระบุหัวข้อ' }}
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                สถานที่: {{ $req->location ?? '-' }}
                            </div>
                            @if ($req->technician)
                                <div class="text-xs text-gray-500">
                                    ช่างผู้ดูแล: {{ $req->technician->name }}
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="{{ route('maintenance.requests.show', $req) }}"
                               class="text-xs text-gray-500 hover:underline">
                                ดูรายละเอียด
                            </a>

                            <a href="{{ route('maintenance.requests.rating.create', $req) }}"
                               class="inline-flex items-center px-3 py-1.5 rounded-md bg-blue-600 text-white text-xs font-medium hover:bg-blue-700">
                                ให้คะแนน
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- งานที่เคยให้คะแนนแล้ว --}}
    <div>
        <h2 class="text-lg font-semibold mb-3">
            งานที่เคยให้คะแนนแล้ว
        </h2>

        @if ($ratedRequests->isEmpty())
            <div class="text-sm text-gray-500 bg-white border rounded-lg p-4">
                ยังไม่มีประวัติการให้คะแนน
            </div>
        @else
            <div class="space-y-3">
                @foreach ($ratedRequests as $req)
                    <div class="bg-white border rounded-lg p-4 flex items-center justify-between">
                        <div>
                            <div class="text-sm text-gray-500">
                                งานซ่อม #{{ $req->id }}
                            </div>
                            <div class="font-semibold text-gray-800">
                                {{ $req->title ?? 'ไม่ระบุหัวข้อ' }}
                            </div>
                            @if ($req->rating)
                                <div class="text-xs text-gray-500 mt-1">
                                    คะแนนที่ให้:
                                    <span class="font-medium text-yellow-600">
                                        {{ $req->rating->score }} / 5
                                    </span>
                                </div>
                                @if ($req->rating->comment)
                                    <div class="text-xs text-gray-500 mt-1">
                                        ความคิดเห็น: "{{ \Illuminate\Support\Str::limit($req->rating->comment, 80) }}"
                                    </div>
                                @endif
                            @endif
                        </div>

                        <a href="{{ route('maintenance.requests.show', $req) }}"
                           class="text-xs text-gray-500 hover:underline">
                            ดูรายละเอียด
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
