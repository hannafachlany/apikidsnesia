@php
    $token = request()->bearerToken();
    $pelanggan = \App\Models\Pelanggan::where('token', $token)->first();
@endphp

<h3>Hai {{ $pelanggan->nama_pelanggan ?? 'Pelanggan' }},</h3>
<p>Bukti transfer kamu sudah kami terima.</p>
<p>Verifikasi akan dilakukan oleh admin:</p>
<ul>
    <li>Jika dikirim antara jam 07.00 – 21.00, verifikasi 1–10 menit kemudian.</li>
    <li>Jika di luar jam itu, akan diverifikasi keesokan harinya.</li>
</ul>

