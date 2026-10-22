<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <title>Jurnal PKL</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        h1 {
            text-align: center;
            margin-bottom: 5px;
        }

        .info {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 7px;
            vertical-align: top;
        }

        th {
            background: #eeeeee;
            text-align: center;
        }

        .dokumentasi-img {
            max-width: 80px;
            max-height: 80px;
        }

    </style>

</head>

<body>

    <h1>JURNAL PKL</h1>

    <div class="info">

        <strong>Nama:</strong>
        {{ $user->name }}

        <br>

        <strong>Email:</strong>
        {{ $user->email }}

    </div>

    <table>

        <thead>

            <tr>

                <th>No</th>
                <th>Tanggal</th>
                <th>Hari</th>
                <th>Unit Kerja</th>
                <th>Catatan</th>
                <th>Dokumentasi</th>

            </tr>

        </thead>

        <tbody>

            @forelse($journals as $journal)

                <tr>

                    <td style="text-align:center;">
                        {{ $loop->iteration }}
                    </td>

                    <td>
                        {{ \Carbon\Carbon::parse($journal->tanggal)->format('d-m-Y') }}
                    </td>

                    <td>
                        {{ $journal->hari }}
                    </td>

                    <td>
                        {!! nl2br(e($journal->unit_kerja)) !!}
                    </td>

                    <td>
                        {!! nl2br(e($journal->catatan ?? '-')) !!}
                    </td>

                    <td style="text-align:center;">
                        @if($journal->dokumentasi)
                            @php
                                $dokumentasiPath = public_path('storage/' . ltrim($journal->dokumentasi, '/'));
                            @endphp
                            @if(file_exists($dokumentasiPath))
                                <img src="{{ $dokumentasiPath }}" class="dokumentasi-img" alt="Dokumentasi">
                            @else
                                Ada
                            @endif
                        @else
                            -
                        @endif
                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="6" style="text-align:center;">
                        Belum ada data jurnal.
                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>

</body>

</html>