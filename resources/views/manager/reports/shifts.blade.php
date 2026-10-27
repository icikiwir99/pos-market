@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i data-lucide="history" class="w-7 h-7 text-purple-600"></i>
                AUDIT LOG SHIFT KASIR & SELISIH KAS
            </h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Pantau kesesuaian uang fisik kasir di laci uang vs rekapan sistem saat pergantian shift</p>
        </div>

        <button onclick="window.print()" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-2">
            <i data-lucide="printer" class="w-4 h-4"></i>
            <span>Cetak Log Shift</span>
        </button>
    </div>

    <!-- Shifts Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Kasir</th>
                        <th class="py-3 px-4">Waktu Buka Shift</th>
                        <th class="py-3 px-4">Waktu Tutup Shift</th>
                        <th class="py-3 px-4 text-right">Modal Awal Kas</th>
                        <th class="py-3 px-4 text-right">Ekspektasi Sistem</th>
                        <th class="py-3 px-4 text-right">Fisik Dihitung</th>
                        <th class="py-3 px-4 text-right">Selisih Kas</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4">Catatan Kasir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($shifts as $s)
                    <tr class="hover:bg-slate-50">
                        <td class="py-3 px-4 font-bold text-slate-900">{{ $s->user->name }}</td>
                        <td class="py-3 px-4 font-mono text-slate-600">{{ $s->opened_at->format('d/m/Y H:i') }}</td>
                        <td class="py-3 px-4 font-mono text-slate-600">
                            {{ $s->closed_at ? $s->closed_at->format('d/m/Y H:i') : '-' }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-slate-700">
                            Rp {{ number_format($s->starting_cash, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-blue-700">
                            Rp {{ number_format($s->ending_cash_expected, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">
                            {{ $s->ending_cash_actual !== null ? 'Rp ' . number_format($s->ending_cash_actual, 0, ',', '.') : '-' }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-black">
                            @if($s->difference === null)
                                <span class="text-slate-400">-</span>
                            @elseif($s->difference == 0)
                                <span class="text-emerald-600 font-bold">Rp 0 (Pas)</span>
                            @elseif($s->difference > 0)
                                <span class="text-emerald-700 font-bold">+ Rp {{ number_format($s->difference, 0, ',', '.') }} (Lebih)</span>
                            @else
                                <span class="text-rose-600 font-bold">- Rp {{ number_format(abs($s->difference), 0, ',', '.') }} (Kurang)</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($s->status === 'open')
                                <span class="bg-emerald-100 text-emerald-800 text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full animate-pulse">
                                    Sedang Aktif
                                </span>
                            @else
                                <span class="bg-slate-100 text-slate-700 text-[10px] font-bold uppercase px-2 py-0.5 rounded-full">
                                    Ditutup
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-500 italic max-w-xs truncate">
                            {{ $s->notes ?: '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-10 text-slate-400">
                            Belum ada catatan shift kasir.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">
            {{ $shifts->links() }}
        </div>
    </div>

</div>
@endsection
