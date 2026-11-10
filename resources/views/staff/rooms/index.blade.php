@extends('layouts.app')
@section('title', 'Status Kamar')
@section('heading', 'Status Kamar')

@section('content')
<p class="mb-4 max-w-3xl text-sm text-slate-500">Kondisi setiap unit hari ini. Setelah check-out kamar otomatis berstatus <strong>Sedang dibersihkan</strong>; tandai <strong>Siap</strong> bila sudah bersih. Kamar yang sedang <strong>Maintenance</strong> tidak dapat dipesan.</p>
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    @foreach ($rooms as $row)
        @php $room = $row['room']; @endphp
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-lg font-extrabold text-slate-900">{{ $room->code }}</p>
                    <p class="text-xs text-slate-500">{{ $room->type->name }} · maks. {{ $room->type->capacity }} orang</p>
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $row['color'] }}">{{ $row['label'] }}</span>
            </div>
            @if ($row['reservation'])
                <a href="{{ route('reservations.show', $row['reservation']) }}" class="mt-3 block rounded-xl bg-slate-50 p-3 text-xs hover:bg-slate-100">
                    <span class="font-semibold text-slate-800">{{ $row['reservation']->guest_name }}</span><br>
                    {{ $row['reservation']->code }} · {{ $row['reservation']->check_in->translatedFormat('d M') }} → {{ $row['reservation']->check_out->translatedFormat('d M') }}
                </a>
            @endif
            <form method="POST" action="{{ route('rooms.status', $room) }}" class="mt-4 flex gap-2">
                @csrf
                <select name="status" class="flex-1 rounded-xl border-slate-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                    @foreach (\App\Models\Room::STATUSES as $key => $label)
                        <option value="{{ $key }}" @selected($room->status === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="rounded-xl bg-brand-600 px-4 text-sm font-bold text-white hover:bg-brand-700">Simpan</button>
            </form>
        </div>
    @endforeach
</div>
@endsection
