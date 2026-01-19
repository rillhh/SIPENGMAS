<!DOCTYPE html>
<html>
<head>
    <title>Lembar Pengesahan</title>
    <style>
        @page { margin: 1cm 2cm 1cm 2cm; }

        body { font-family: "Times New Roman", Times, serif; font-size: 12pt; line-height: 1.15; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        
        /* 2. TABEL */
        table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        td { vertical-align: top; padding: 4px; }
        
        /* Border */
        .table-bordered { border: 1px solid black; }
        .table-bordered td { border: 1px solid black; }
        
        /* 3. LEBAR KOLOM LABEL */
        .col-label { width: 35%; } 
        .col-sep { width: 10px; text-align: center; }
        
        .header-logo { width: 200px; margin-bottom: 10px; }
        
        /* Utilitas indentasi */
        .indent { padding-left: 20px; }
    </style>
</head>
<body>
    {{-- HEADER --}}
    <div style="text-align: left; margin-bottom: 5px;">
        <img src="{{ public_path('images/logoyarsi.jpg') }}" style="width: 200px;">
    </div>
    
    <div class="text-center" style="margin-bottom: 10px;">
        <div class="text-bold">LEMBAR PENGESAHAN PROPOSAL</div>
        <div class="text-bold">PENGABDIAN KEPADA MASYARAKAT</div>
    </div>

    <table class="table-bordered">
        {{-- JUDUL --}}
        <tr>
            <td class="text-bold col-label">Judul</td>
            <td class="col-sep">:</td>
            <td class="text-bold" style="text-transform: uppercase;">{{ $p->judul }}</td>
        </tr>

        {{-- KETUA PELAKSANA --}}
        <tr>
            <td class="text-bold">Nama Lengkap Ketua</td>
            <td class="col-sep">:</td>
            <td>{{ $ketua->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td class="text-bold">NIDN</td>
            <td class="col-sep">:</td>
            <td>{{ $ketua->nidn ?? '-' }}</td>
        </tr>
        <tr>
            <td class="text-bold">Program Studi</td>
            <td class="col-sep">:</td>
            <td>{{ $ketua->prodi ?? '-' }}</td>
        </tr>
        <tr>
            <td class="text-bold">Jabatan Fungsional</td>
            <td class="col-sep">:</td>
            <td>{{ $ketua->jabatan_fungsional }}</td>
        </tr>

        {{-- ANGGOTA DOSEN --}}
        @php $no = 1; @endphp
        @foreach($dosen as $d)
            @if($d->id != ($ketua->id ?? 0))
                <tr>
                    <td class="text-bold">Anggota ({{ $no++ }})</td>
                    <td class="col-sep">:</td>
                    <td></td> 
                </tr>
                <tr>
                    <td class="text-bold indent">Nama Lengkap</td>
                    <td class="col-sep">:</td>
                    <td>{{ $d->nama }}</td>
                </tr>
                <tr>
                    <td class="text-bold indent">NIDN</td>
                    <td class="col-sep">:</td>
                    <td>{{ $d->nidn }}</td>
                </tr>
            @endif
        @endforeach

        {{-- MAHASISWA & TENDIK --}}
        <tr>
            <td class="text-bold">Jumlah Mahasiswa yang terlibat</td>
            <td class="col-sep">:</td>
            <td>{{ $mahasiswa->count() }} Orang</td>
        </tr>
        <tr>
            <td class="text-bold">Jumlah Tendik yang terlibat</td>
            <td class="col-sep">:</td>
            <td>{{ $p->jumlah_tendik}} Orang</td>
        </tr>

        {{-- MITRA --}}
        <tr>
            <td class="text-bold">Institusi Mitra</td>
            <td class="col-sep">:</td>
            <td></td>
        </tr>
        <tr>
            <td class="text-bold indent">Nama Institusi Mitra</td>
            <td class="col-sep">:</td>
            <td>{{ $p->nama_institusi_mitra }}</td>
        </tr>
        <tr>
            <td class="text-bold indent">Alamat</td>
            <td class="col-sep">:</td>
            <td>{{ $p->alamat_mitra }}</td>
        </tr>
        <tr>
            <td class="text-bold indent">Penanggung Jawab</td>
            <td class="col-sep">:</td>
            <td>{{ $p->penanggung_jawab_mitra }}</td>
        </tr>

        {{-- BIAYA --}}
        <tr>
            <td class="text-bold">Biaya Keseluruhan</td>
            <td class="col-sep">:</td>
            <td>Rp {{ number_format($p->total_biaya, 0, ',', '.') }}</td>
        </tr>
    </table>

    {{-- TANDA TANGAN --}}
    <table style="border: none; margin-top: 10px;">
        <tr>
            <td width="50%" style="padding: 10;"class="text-bold">
                Mengetahui,<br>
                {{ $p->jabatan_mengetahui ?? 'Dekan / Kepala Pusat' }}<br>
                <div style="height: 50px;"></div>
                <u>( {{ $p->nama_mengetahui ?? '.........................' }} )</u><br>
                NIP/LIK. {{ $p->nip_mengetahui ?? '.........................' }}
            </td>
            <td width="50%" style="text-align: left; padding-left: 50px; padding-top: 10;" class="text-bold">
                Jakarta, {{ date('d F Y') }}<br>
                Ketua Pelaksana<br>
                <div style="height: 50px;"></div>
                <u>( {{ $ketua->nama ?? '.........................' }} )</u><br>
                NIDN. {{ $ketua->nidn ?? '.........................' }}
            </td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center; padding-top: 10px;" class="text-bold">
                Menyetujui,<br>
                {{ $p->jabatan_menyetujui ?? 'Wakil Rektor III' }}<br>
                <div style="height: 50px;"></div>
                <u>( {{ $p->nama_menyetujui ?? '.........................' }} )</u><br>
                NIP/LIK. {{ $p->nip_menyetujui ?? '.........................' }}
            </td>
        </tr>
    </table>

</body>
</html>