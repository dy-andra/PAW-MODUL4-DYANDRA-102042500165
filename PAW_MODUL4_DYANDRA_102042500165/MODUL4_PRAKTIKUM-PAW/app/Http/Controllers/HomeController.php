<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Menampilkan halaman katalog buku dengan fitur pencarian, filter, sorting, dan paginasi.
     */
    public function index(Request $request)
    {
        // Query dasar untuk mengambil data buku
        $query = Buku::query();

        // 1. Pencarian berdasarkan judul, penulis, atau penerbit
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                ->orWhere('penulis', 'like', "%{$search}%")
                ->orWhere('penerbit', 'like', "%{$search}%");
            });
        }

        // 2. Filter berdasarkan penerbit
        if ($request->filled('penerbit')) {
            $query->where('penerbit', $request->penerbit);
        }

        // Filter berdasarkan ketersediaan stok
        if ($request->status == 'tersedia') {
            $query->where('stok', '>', 0);
        } elseif ($request->status == 'habis') {
            $query->where('stok', '<=', 0);
        }

        // 3. Pengurutan data buku
        $sort = $request->sort;

        if ($sort == 'judul') {
            $query->orderBy('judul', 'asc');
        } elseif ($sort == 'tahun') {
            $query->orderBy('tahun_terbit', 'desc');
        } elseif ($sort == 'stok') {
            $query->orderBy('stok', 'desc');
        } else {
            $query->latest();
        }

        // 4. Pagination sebanyak 8 data
        $buku = $query->paginate(8)->withQueryString();

        // 5. Mengambil daftar penerbit unik
        $listPenerbit = Buku::select('penerbit')
            ->distinct()
            ->orderBy('penerbit')
            ->pluck('penerbit');

        // 6. Mengirim data ke halaman home
        return view('home', compact('buku', 'listPenerbit'));
    }

    /**
     * Menampilkan halaman detail informasi sebuah buku untuk publik/mahasiswa.
     */
    public function show(Buku $buku)
    {
        // TODO 3: Kembalikan view 'home_show' membawa data $buku
        return view('home_show', compact('buku'));
    }
}
