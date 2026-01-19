<table>
    <thead>
    {{-- 
        [BARIS 1-9] AREA LOGO
        Loop 9 kali = Baris 1 sampai 9 kosong untuk di-merge PHP.
    --}}
    @for ($i = 0; $i < 9; $i++)
    <tr>
        <td></td><td></td><td></td><td></td><td></td><td></td><td></td>
    </tr>
    @endfor

    {{-- 
        [BARIS 10-12] JUDUL LAPORAN 
        Dimulai tepat di Baris 10 sesuai permintaan.
    --}}
    <tr>
        <td colspan="7" style="font-weight: bold; font-size: 16px; text-align: center; vertical-align: middle;">
            UNIVERSITAS YARSI
        </td>
    </tr>
    <tr>
        <td colspan="7" style="font-weight: bold; font-size: 14px; text-align: center; vertical-align: middle;">
            REKAPITULASI PROPOSAL PENGABDIAN KEPADA MASYARAKAT
        </td>
    </tr>
    <tr>
        <td colspan="7" style="text-align: center; vertical-align: middle;">
            Status: {{ $status }} | Tahun: {{ $year }}
        </td>
    </tr>

    {{-- [BARIS 13] SPASI PEMISAH --}}
    <tr><td colspan="7" style="height: 10px;"></td></tr>

    {{-- [BARIS 14] HEADER TABEL --}}
    <tr>
        <th>No</th>
        <th>Dosen Pengusul</th>
        <th>NIDN</th>
        <th>Judul Proposal</th>
        <th>Dana</th>
        <th>Tanggal Masuk</th>
        @if($status == 'Ditolak')
            <th>Alasan Penolakan</th>
        @else
            <th>Status</th>
        @endif
    </tr>
    </thead>
    
    <tbody>
        @foreach($data as $index => $row)
        @php
            $totalDana = 0;
            if($row->biaya) {
                $totalDana = ($row->biaya->honor_output ?? 0) + 
                             ($row->biaya->belanja_non_operasional ?? 0) + 
                             ($row->biaya->bahan_habis_pakai ?? 0) + 
                             ($row->biaya->transportasi ?? 0);
            }
        @endphp
        <tr>
            <td style="text-align: center;">{{ $index + 1 }}</td>
            <td>{{ $row->user->name ?? 'User Tidak Ditemukan' }}</td>
            <td style="text-align: center;">{{ isset($row->user->nidn) ? " " . $row->user->nidn : '-' }}</td>
            <td>{{ $row->identitas->judul ?? 'Judul Belum Diisi' }}</td>
            <td>{{ $totalDana }}</td> 
            <td style="text-align: center;">{{ $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('d-m-Y') : '-' }}</td>
            
            @if($status == 'Ditolak')
                <td style="color: red;">{{ $row->feedback ?? '-' }}</td>
            @else
                <td>{{ $status }}</td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>