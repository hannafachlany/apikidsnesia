<h3>Hai {{ $pembelian->pelanggan->nama_pelanggan }},</h3>
<p>Terima kasih telah membeli tiket event di Kidsnesia!</p>
<p>Berikut adalah detail pembelianmu:</p>

<ul>
    @foreach ($pembelian->detailEvent as $item)
        @php
            $tanggal = \Carbon\Carbon::parse($item->event->tanggal_event)->format('d-m-Y');
            $jamArray = ['09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '13:00', '13:30', '14:00', '14:30', '15:00'];
            $jamAcak = $jamArray[array_rand($jamArray)];
        @endphp
        <li>
            {{ $item->event->nama_event }} - {{ $tanggal }} (Jam kedatangan: {{ $jamAcak }})  
            <br>(Jumlah: {{ $item->jumlah }})
        </li>
    @endforeach
</ul>

<p>Total: Rp{{ number_format($pembelian->total_pembelian, 0, ',', '.') }}</p>
<p>Kami tunggu kedatanganmu! 🎉</p>
