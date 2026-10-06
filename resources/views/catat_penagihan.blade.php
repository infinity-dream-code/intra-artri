<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catat Penagihan - Monitoring Kepsek</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root { --bg:#f1f5f9; --card:#fff; --text:#0f172a; --muted:#64748b; --accent:#2563eb; --border:#e2e8f0; }
        * { box-sizing: border-box; }
        body { margin:0; font-family:'Plus Jakarta Sans',sans-serif; background:var(--bg); color:var(--text); }
        .topbar { background:#fff; border-bottom:1px solid var(--border); padding:14px 20px; display:flex; align-items:center; gap:14px; }
        .topbar a { color:var(--accent); text-decoration:none; font-weight:600; font-size:.88rem; }
        .topbar h1 { margin:0; font-size:1.05rem; font-weight:800; }
        .wrap { max-width:640px; margin:24px auto; padding:0 16px 40px; }
        .card { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:22px; }
        .meta { display:grid; grid-template-columns:1fr 1fr; gap:10px 16px; background:#f8fafc; border:1px solid var(--border); border-radius:12px; padding:12px 14px; margin-bottom:16px; }
        .meta span { display:block; font-size:.72rem; color:var(--muted); font-weight:600; }
        .meta b { font-size:.88rem; }
        .meta .sisa { color:#dc2626; }
        .field { margin-bottom:14px; }
        .field > label { display:block; font-size:.82rem; font-weight:700; margin-bottom:8px; }
        .field > label em { color:#dc2626; font-style:normal; }
        input[type=date], input[type=text], textarea { width:100%; border:1px solid var(--border); border-radius:10px; padding:10px 12px; font:inherit; }
        .radios { display:grid; gap:8px; }
        .radios.inline { grid-template-columns:1fr 1fr; }
        .radios label { display:flex; gap:8px; align-items:center; font-size:.86rem; }
        .counter { text-align:right; font-size:.72rem; color:var(--muted); }
        .actions { display:flex; justify-content:flex-end; gap:10px; margin-top:8px; }
        .btn-cancel, .btn-save { border-radius:10px; padding:10px 14px; font:inherit; font-weight:700; cursor:pointer; }
        .btn-cancel { border:1px solid var(--border); background:#fff; }
        .btn-save { border:none; background:var(--accent); color:#fff; }
    </style>
</head>
<body>
    <div class="topbar">
        <a href="{{ route('kepsek.tagihan-periode') }}"><i class="fas fa-arrow-left"></i> Kembali</a>
        <h1>Catat Hasil Penagihan</h1>
    </div>
    <div class="wrap">
        <div class="card">
            <div class="meta">
                <div><span>Nama Santri</span><b>{{ $nama !== '' ? strtoupper($nama) : '-' }}</b></div>
                <div><span>Kelas / Program</span><b>{{ $kelas !== '' ? $kelas : '-' }}</b></div>
                <div><span>Tagihan Per</span><b>{{ trim(($tagihan ?: '-') . ' ' . ($bta ?: '')) }}</b></div>
                <div><span>Sisa Tagihan</span><b class="sisa">{{ $sisa !== '' ? 'Rp ' . number_format((float)$sisa, 0, ',', '.') : '-' }}</b></div>
            </div>
            <form id="formCatat">
                <input type="hidden" name="custid" value="{{ $custid }}">
                <input type="hidden" name="nocust" value="{{ $nocust ?? '' }}">
                <div class="field">
                    <label>Tanggal Komunikasi <em>*</em></label>
                    <input type="date" name="tanggal_komunikasi" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="field">
                    <label>Media Komunikasi <em>*</em></label>
                    <div class="radios inline">
                        <label><input type="radio" name="media_komunikasi" value="WhatsApp" checked> WhatsApp</label>
                        <label><input type="radio" name="media_komunikasi" value="Telepon"> Telepon</label>
                    </div>
                </div>
                <div class="field">
                    <label>Hasil Komunikasi <em>*</em></label>
                    <div class="radios">
                        <label><input type="radio" name="hasil_komunikasi" value="Akan melakukan pembayaran" checked> Akan melakukan pembayaran</label>
                        <label><input type="radio" name="hasil_komunikasi" value="Sudah melakukan pembayaran"> Sudah melakukan pembayaran</label>
                        <label><input type="radio" name="hasil_komunikasi" value="Meminta waktu pembayaran"> Meminta waktu pembayaran</label>
                        <label><input type="radio" name="hasil_komunikasi" value="Mengalami kendala pembayaran"> Mengalami kendala pembayaran</label>
                        <label><input type="radio" name="hasil_komunikasi" value="Tidak dapat dihubungi"> Tidak dapat dihubungi</label>
                    </div>
                </div>
                <div class="field">
                    <label>Rencana Pembayaran</label>
                    <input type="text" name="rencana_pembayaran" maxlength="255" placeholder="Contoh: 25 Agustus 2026 / Angsuran 2 kali">
                </div>
                <div class="field">
                    <label>Catatan</label>
                    <textarea name="catatan" id="catatan" maxlength="250" rows="4" placeholder="Tuliskan catatan hasil komunikasi..."></textarea>
                    <div class="counter"><span id="cnt">0</span>/250</div>
                </div>
                <div class="actions">
                    <a class="btn-cancel" href="{{ route('kepsek.tagihan-periode') }}" style="text-decoration:none;display:inline-flex;align-items:center;">Batal</a>
                    <button type="submit" class="btn-save"><i class="fas fa-floppy-disk"></i> Simpan Hasil Penagihan</button>
                </div>
            </form>
        </div>
    </div>
<script>
const storeUrl = @json(route('kepsek.tagihan-periode.catat-penagihan.store'));
const csrf = @json(csrf_token());
document.getElementById('catatan').addEventListener('input', function(){ document.getElementById('cnt').textContent = this.value.length; });
document.getElementById('formCatat').addEventListener('submit', async function(e){
    e.preventDefault();
    const fd = new FormData(this);
    const payload = Object.fromEntries(fd.entries());
    try {
        const res = await fetch(storeUrl, {
            method:'POST',
            headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':csrf},
            body: JSON.stringify(payload)
        });
        const json = await res.json().catch(()=>({}));
        if (!res.ok || !json.success) throw new Error(json.message || 'Gagal menyimpan');
        await Swal.fire({icon:'success', title:'Tersimpan', text: json.message || 'Berhasil', confirmButtonColor:'#2563eb'});
        window.location.href = @json(route('kepsek.tagihan-periode'));
    } catch (err) {
        Swal.fire({icon:'error', title:'Gagal', text: err.message, confirmButtonColor:'#2563eb'});
    }
});
</script>
</body>
</html>
