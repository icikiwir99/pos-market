@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="{ createModal: false }">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i data-lucide="users" class="w-7 h-7 text-purple-600"></i>
                MANAJEMEN KARYAWAN & HAK AKSES
            </h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Kelola akun kasir, staf gudang, dan manager serta status keaktifan user</p>
        </div>

        <button @click="createModal = true" class="px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold transition-all shadow-md flex items-center gap-2">
            <i data-lucide="user-plus" class="w-4 h-4"></i>
            <span>Tambah Karyawan Baru</span>
        </button>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Nama Lengkap</th>
                        <th class="py-3 px-4">Username & Email</th>
                        <th class="py-3 px-4 text-center">Role / Hak Akses</th>
                        <th class="py-3 px-4">No. Telepon</th>
                        <th class="py-3 px-4 text-center">Status Akun</th>
                        <th class="py-3 px-4 text-center">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($users as $u)
                    <tr class="hover:bg-slate-50">
                        <td class="py-3 px-4 font-bold text-slate-900">{{ $u->name }}</td>
                        <td class="py-3 px-4">
                            <span class="font-mono font-bold text-slate-800 block">{{ $u->username }}</span>
                            <span class="text-[11px] text-slate-400 block">{{ $u->email }}</span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($u->role === 'manager')
                                <span class="bg-purple-100 text-purple-800 font-bold px-2.5 py-1 rounded-full text-[10px] uppercase">Manager</span>
                            @elseif($u->role === 'gudang')
                                <span class="bg-amber-100 text-amber-800 font-bold px-2.5 py-1 rounded-full text-[10px] uppercase">Gudang</span>
                            @else
                                <span class="bg-blue-100 text-blue-800 font-bold px-2.5 py-1 rounded-full text-[10px] uppercase">Kasir</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-mono text-slate-600">{{ $u->phone ?: '-' }}</td>
                        <td class="py-3 px-4 text-center">
                            @if($u->is_active)
                                <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded-full">Aktif</span>
                            @else
                                <span class="bg-rose-100 text-rose-800 text-[10px] font-bold px-2 py-0.5 rounded-full">Nonaktif</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($u->id !== auth()->id())
                                <form action="{{ route('manager.users.toggle', $u->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit" 
                                            class="px-2.5 py-1 text-xs font-bold rounded-lg border transition-colors {{ $u->is_active ? 'bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' }}">
                                        {{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            @else
                                <span class="text-slate-400 text-[11px] italic">Akun Anda</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">
            {{ $users->links() }}
        </div>
    </div>

    <!-- MODAL TAMBAH USER -->
    <div x-show="createModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-md w-full overflow-hidden" @click.outside="createModal = false">
            <div class="bg-purple-600 text-white px-5 py-3.5 flex items-center justify-between">
                <h3 class="text-sm font-extrabold flex items-center gap-2">
                    <i data-lucide="user-plus" class="w-4 h-4 text-white"></i>
                    TAMBAH AKUN KARYAWAN
                </h3>
                <button @click="createModal = false" class="text-white/80 hover:text-white">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <form action="{{ route('manager.users.store') }}" method="POST" class="p-5 space-y-3.5">
                @csrf
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Nama Lengkap</label>
                    <input type="text" name="name" required placeholder="Contoh: Rian Kasir Siang"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold focus:ring-2 focus:ring-purple-600 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Username Login</label>
                        <input type="text" name="username" required placeholder="kasir3"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono font-bold focus:ring-2 focus:ring-purple-600 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Role / Jabatan</label>
                        <select name="role" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-purple-600 focus:outline-none">
                            <option value="kasir">Kasir</option>
                            <option value="gudang">Gudang</option>
                            <option value="manager">Manager</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Email</label>
                    <input type="email" name="email" required placeholder="karyawan@indomaret.local"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-purple-600 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Password</label>
                        <input type="password" name="password" required placeholder="••••••••" minlength="6"
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono focus:ring-2 focus:ring-purple-600 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">No. HP / WhatsApp</label>
                        <input type="text" name="phone" placeholder="0812..."
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-purple-600 focus:outline-none">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2.5 rounded-xl text-xs uppercase tracking-wider shadow-md">
                        Simpan Akun Karyawan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
