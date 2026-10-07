<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ImportPenagihanController;
use App\Http\Controllers\KelolaUserController;
use App\Http\Controllers\LaporanCashlessController;
use App\Http\Controllers\MonitoringKepsekController;
use App\Http\Controllers\PerizinanController;
use App\Http\Controllers\PresensiSholatController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TagihanPeriodeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'showLogin'])->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['check.auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/dashboard-presensi-sholat', function () {
        return view('dashboard_presensi_sholat');
    })->name('dashboard.presensi-sholat');

    Route::get('/dashboard-monitoring-kepsek', [MonitoringKepsekController::class, 'showDashboard'])->name('dashboard.monitoring-kepsek');
    Route::get('/dashboard-monitoring-kepsek/data', [MonitoringKepsekController::class, 'dashboardData'])->name('dashboard.monitoring-kepsek.data');

    Route::get('/kepsek/tagihan', [MonitoringKepsekController::class, 'showTagihan'])->name('kepsek.tagihan');
    Route::get('/kepsek/tagihan/filters', [MonitoringKepsekController::class, 'tagihanFilterOptions'])->name('kepsek.tagihan.filters');
    Route::get('/kepsek/tagihan/siswa', [MonitoringKepsekController::class, 'siswaList'])->name('kepsek.tagihan.siswa');
    Route::get('/kepsek/tagihan/siswa/detail', [MonitoringKepsekController::class, 'siswaTagihan'])->name('kepsek.tagihan.siswa-detail');
    Route::post('/kepsek/tagihan/detail', [MonitoringKepsekController::class, 'tagihanDetail'])->name('kepsek.tagihan.detail');
    Route::get('/kepsek/tagihan/summary', [MonitoringKepsekController::class, 'tagihanSummary'])->name('kepsek.tagihan.summary');
    Route::get('/kepsek/tagihan/export-excel', [MonitoringKepsekController::class, 'exportTagihanExcel'])->name('kepsek.tagihan.export-excel');
    Route::get('/kepsek/tagihan/export-pdf', [MonitoringKepsekController::class, 'exportTagihanPdf'])->name('kepsek.tagihan.export-pdf');
    Route::get('/kepsek/tagihan/siswa-uang-masuk', [MonitoringKepsekController::class, 'siswaUangMasuk'])->name('kepsek.tagihan.siswa-uang-masuk');
    Route::get('/tagihan/siswa/saldo', [MonitoringKepsekController::class, 'siswaSaldo'])
        ->name('kepsek.tagihan.siswa-saldo');

    Route::get('/kepsek/tagihan-periode', [TagihanPeriodeController::class, 'showTagihan'])->name('kepsek.tagihan-periode');
    Route::get('/kepsek/tagihan-periode/filters', [TagihanPeriodeController::class, 'tagihanFilterOptions'])->name('kepsek.tagihan-periode.filters');
    Route::get('/kepsek/tagihan-periode/siswa', [TagihanPeriodeController::class, 'siswaList'])->name('kepsek.tagihan-periode.siswa');
    Route::get('/kepsek/tagihan-periode/siswa/detail', [TagihanPeriodeController::class, 'siswaTagihan'])->name('kepsek.tagihan-periode.siswa-detail');
    Route::post('/kepsek/tagihan-periode/detail', [TagihanPeriodeController::class, 'tagihanDetail'])->name('kepsek.tagihan-periode.detail');
    Route::get('/kepsek/tagihan-periode/summary', [TagihanPeriodeController::class, 'tagihanSummary'])->name('kepsek.tagihan-periode.summary');
    Route::get('/kepsek/tagihan-periode/export-excel', [TagihanPeriodeController::class, 'exportTagihanExcel'])->name('kepsek.tagihan-periode.export-excel');
    Route::get('/kepsek/tagihan-periode/export-pdf', [TagihanPeriodeController::class, 'exportTagihanPdf'])->name('kepsek.tagihan-periode.export-pdf');
    Route::get('/kepsek/tagihan-periode/siswa/laporan', [TagihanPeriodeController::class, 'exportSiswaLaporan'])->name('kepsek.tagihan-periode.siswa-laporan');
    Route::get('/kepsek/tagihan-periode/invoice', [TagihanPeriodeController::class, 'exportInvoice'])->name('kepsek.tagihan-periode.invoice');
    Route::get('/kepsek/tagihan-periode/siswa-uang-masuk', [TagihanPeriodeController::class, 'siswaUangMasuk'])->name('kepsek.tagihan-periode.siswa-uang-masuk');
    Route::get('/tagihan/siswa/periode/saldo', [TagihanPeriodeController::class, 'siswaSaldo'])->name('kepsek.tagihan-periode.siswa-saldo');
    Route::get('/kepsek/tagihan-periode/kelas', [TagihanPeriodeController::class, 'kelasBySekolah'])
        ->name('kepsek.tagihan-periode.kelas');
    Route::get('/kepsek/tagihan-periode/catat-penagihan', [TagihanPeriodeController::class, 'showCatatPenagihan'])
        ->name('kepsek.tagihan-periode.catat-penagihan');
    Route::post('/kepsek/tagihan-periode/catat-penagihan', [TagihanPeriodeController::class, 'storeCatatPenagihan'])
        ->name('kepsek.tagihan-periode.catat-penagihan.store');
    Route::get('/kepsek/tagihan-periode/laporan-penagihan', [TagihanPeriodeController::class, 'showLaporanPenagihan'])
        ->name('kepsek.tagihan-periode.laporan-penagihan');
    Route::get('/kepsek/tagihan-periode/riwayat-penagihan', [TagihanPeriodeController::class, 'riwayatPenagihan'])
        ->name('kepsek.tagihan-periode.riwayat-penagihan');

    Route::get('/kepsek/kelola-user', [KelolaUserController::class, 'show'])->name('kepsek.kelola-user');
    Route::get('/kepsek/kelola-user/data', [KelolaUserController::class, 'index'])->name('kepsek.kelola-user.data');
    Route::post('/kepsek/kelola-user', [KelolaUserController::class, 'store'])->name('kepsek.kelola-user.store');
    Route::post('/kepsek/kelola-user/update', [KelolaUserController::class, 'update'])->name('kepsek.kelola-user.update');
    Route::post('/kepsek/kelola-user/delete', [KelolaUserController::class, 'destroy'])->name('kepsek.kelola-user.delete');

    Route::get('/kepsek/import-penagihan', [ImportPenagihanController::class, 'show'])->name('kepsek.import-penagihan');
    Route::get('/kepsek/import-penagihan/data', [ImportPenagihanController::class, 'data'])->name('kepsek.import-penagihan.data');
    Route::get('/kepsek/import-penagihan/template', [ImportPenagihanController::class, 'downloadTemplate'])->name('kepsek.import-penagihan.template');
    Route::post('/kepsek/import-penagihan', [ImportPenagihanController::class, 'import'])->name('kepsek.import-penagihan.store');

    Route::get('/laporan-cashless', [LaporanCashlessController::class, 'show'])->name('laporan-cashless');
    Route::get('/laporan-cashless/filters', [LaporanCashlessController::class, 'filters'])->name('laporan-cashless.filters');
    Route::get('/laporan-cashless/kelas', [LaporanCashlessController::class, 'kelasBySekolah'])->name('laporan-cashless.kelas');
    Route::get('/laporan-cashless/detail', [LaporanCashlessController::class, 'detail'])->name('laporan-cashless.detail');
    Route::get('/laporan-cashless/data', [LaporanCashlessController::class, 'data'])->name('laporan-cashless.data');
    Route::get('/laporan-cashless/summary', [LaporanCashlessController::class, 'summary'])->name('laporan-cashless.summary');
    Route::get('/laporan-cashless/export-excel', [LaporanCashlessController::class, 'exportExcel'])->name('laporan-cashless.export-excel');
    Route::get('/laporan-cashless/export-pdf', [LaporanCashlessController::class, 'exportPdf'])->name('laporan-cashless.export-pdf');

    Route::get('/presensi-sholat/qr', [PresensiSholatController::class, 'showQr'])->name('presensi-sholat.qr');
    Route::post('/presensi-sholat/post-sholat', [PresensiSholatController::class, 'postSholat'])->name('presensi-sholat.post-sholat');

    Route::get('/presensi-haid/qr', [PresensiSholatController::class, 'showHaidQr'])->name('presensi-haid.qr');
    Route::post('/presensi-haid/post-haid', [PresensiSholatController::class, 'postHaid'])->name('presensi-haid.post-haid');

    Route::get('/log-marifah', [PresensiSholatController::class, 'showLogMarifah'])->name('presensi.log-marifah');
    Route::get('/log-presensi', [PresensiSholatController::class, 'showLogPresensi'])->name('presensi.log-presensi');
    Route::get('/log-presensi/export-excel', [PresensiSholatController::class, 'exportLogPresensiExcel'])->name('presensi.log-presensi.export-excel');
    Route::get('/log-presensi/export-pdf', [PresensiSholatController::class, 'exportLogPresensiPdf'])->name('presensi.log-presensi.export-pdf');

    Route::get('/kelola-presensi', [PresensiSholatController::class, 'showKelolaPresensi'])->name('presensi.kelola');
    Route::get('/kelola-presensi/data', [PresensiSholatController::class, 'kelolaPresensiData'])->name('presensi.kelola.data');
    Route::post('/kelola-presensi/update', [PresensiSholatController::class, 'updatePresensi'])->name('presensi.kelola.update');

    Route::get('/rekap-sholat', [PresensiSholatController::class, 'showRekapSholat'])->name('presensi.rekap-sholat');
    Route::get('/rekap-sholat/data', [PresensiSholatController::class, 'rekapSholatData'])->name('presensi.rekap-sholat.data');
    Route::get('/rekap-sholat/export-excel', [PresensiSholatController::class, 'exportRekapSholatExcel'])->name('presensi.rekap-sholat.export-excel');
    Route::get('/rekap-sholat/export-pdf', [PresensiSholatController::class, 'exportRekapSholatPdf'])->name('presensi.rekap-sholat.export-pdf');

    Route::get('/students/search', [StudentController::class, 'liveSearch'])->name('students.search');

    Route::get('/perizinan-kedatangan', function () {
        return view('perizinan_kedatangan');
    })->name('perizinan.kedatangan');

    Route::post('/perizinan-kedatangan/submit', [PerizinanController::class, 'submitKedatangan'])->name('perizinan.kedatangan.submit');

    Route::get('/perizinan-umum', function () {
        return view('perizinan_umum');
    })->name('perizinan.umum');

    Route::post('/perizinan-umum/submit', [PerizinanController::class, 'submitUmum'])->name('perizinan.umum.submit');

    Route::get('/perizinan-khusus', function () {
        return view('perizinan_khusus');
    })->name('perizinan.khusus');

    Route::post('/perizinan-khusus/submit', [PerizinanController::class, 'submitKhusus'])->name('perizinan.khusus.submit');

    Route::get('/account/ganti-password', [AuthController::class, 'showGantiPassword'])->name('account.ganti-password');
    Route::post('/account/ganti-password', [AuthController::class, 'gantiPassword'])->name('account.ganti-password.post');

    Route::get('/presensi/account/ganti-password', [AuthController::class, 'showGantiPasswordPresensi'])->name('presensi.account.ganti-password');
    Route::post('/presensi/account/ganti-password', [AuthController::class, 'gantiPassword'])->name('presensi.account.ganti-password.post');

    Route::get('/laporan', [PerizinanController::class, 'showLaporan'])->name('laporan');
});
