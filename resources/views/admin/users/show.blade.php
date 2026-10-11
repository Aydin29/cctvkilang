@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Detail Pengguna</h1>
            <a href="{{ route('admin.users.index') }}" class="text-blue-600 hover:text-blue-900 font-bold">Kembali</a>
        </div>

        <div class="bg-white p-8 rounded-lg shadow-lg">
            <div class="mb-4">
                <p class="text-sm font-bold text-gray-700">Nama:</p>
                <p class="text-lg text-gray-900">{{ $user->name }}</p>
            </div>
            <div class="mb-4">
                <p class="text-sm font-bold text-gray-700">Email:</p>
                <p class="text-lg text-gray-900">{{ $user->email }}</p>
            </div>
            <div class="mb-4">
                <p class="text-sm font-bold text-gray-700">Tanggal Dibuat:</p>
                <p class="text-lg text-gray-900">{{ $user->created_at->format('d M Y H:i') }}</p>
            </div>
            <div class="mb-4">
                <p class="text-sm font-bold text-gray-700">Terakhir Diperbarui:</p>
                <p class="text-lg text-gray-900">{{ $user->updated_at->format('d M Y H:i') }}</p>
            </div>
        </div>

        <div class="mt-6 flex space-x-4">
            <a href="{{ route('admin.users.edit', $user->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300 ease-in-out">
                Edit Pengguna
            </a>
            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300 ease-in-out">
                    Hapus Pengguna
                </button>
            </form>
        </div>
    </div>
@endsection
