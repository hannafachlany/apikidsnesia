<h3>Hai {{ $pembelian->pelanggan->nama_pelanggan }},</h3>
<p>Terima kasih telah membeli merchandise di Kidsnesia!</p>

<ul>
    @foreach ($pembelian->detailMerchandise as $item)
        <li>{{ $item->merchandise->nama_merchandise }} - (Jumlah: {{ $item->jumlah }})</li>
    @endforeach
</ul>

<p>Total: Rp{{ number_format($pembelian->total_pembelian, 0, ',', '.') }}</p>
<p>Merchandise kamu akan segera dikemas dan dikirim. ✨</p>
