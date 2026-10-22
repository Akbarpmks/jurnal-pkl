<?php

namespace App\Http\Controllers;

use App\Exports\JournalExport;
use App\Models\Journal;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class ExportController extends Controller
{
    public function index()
    {
        return view('export.index');
    }

    /**
     * Export Excel
     */
    public function excel()
    {
        return Excel::download(
            new JournalExport(Auth::id()),
            'jurnal-pkl.xlsx'
        );
    }

    /**
     * Export PDF
     */
    public function pdf()
    {
        $journals = Journal::where('user_id', Auth::id())
            ->orderBy('tanggal', 'asc')
            ->get();

        $pdf = Pdf::loadView('export.pdf', [
            'journals' => $journals,
            'user' => Auth::user(),
        ]);

        return $pdf->download('jurnal-pkl.pdf');
    }

    /**
     * Membersihkan teks sebelum dimasukkan ke Word.
     *
     * Catatan:
     * Jangan menggunakan htmlspecialchars() atau mengganti
     * karakter & menjadi &amp; di sini.
     *
     * PhpWord yang akan menangani escaping XML saat membuat DOCX
     * (output escaping diaktifkan melalui Settings::setOutputEscapingEnabled()).
     */
    private function cleanText($value)
    {
        if ($value === null) {
            return '-';
        }

        $value = (string) $value;

        /*
        |--------------------------------------------------------------------------
        | Pastikan UTF-8 valid
        |--------------------------------------------------------------------------
        */

        if (!mb_check_encoding($value, 'UTF-8')) {
            $value = mb_convert_encoding(
                $value,
                'UTF-8',
                'UTF-8'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Hapus karakter kontrol yang tidak diperbolehkan XML
        |--------------------------------------------------------------------------
        |
        | XML 1.0 hanya mengizinkan:
        | TAB  (\x09)
        | LF   (\x0A)
        | CR   (\x0D)
        | dan karakter mulai dari spasi.
        |
        */

        $value = preg_replace(
            '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u',
            '',
            $value
        );

        /*
        |--------------------------------------------------------------------------
        | Hapus karakter Unicode yang tidak valid untuk XML
        |--------------------------------------------------------------------------
        */

        $value = preg_replace(
            '/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]/u',
            '',
            $value
        );

        /*
        |--------------------------------------------------------------------------
        | Rapikan spasi
        |--------------------------------------------------------------------------
        */

        $value = trim($value);

        return $value === '' ? '-' : $value;
    }

    /**
     * Export Word
     */
    public function word()
    {
        /*
        |--------------------------------------------------------------------------
        | AMBIL DATA JURNAL USER YANG SEDANG LOGIN
        |--------------------------------------------------------------------------
        */

        $journals = Journal::where('user_id', Auth::id())
            ->orderBy('tanggal', 'asc')
            ->get();

        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | BUAT DOKUMEN WORD
        |--------------------------------------------------------------------------
        */

        $phpWord = new PhpWord();

        /*
        |--------------------------------------------------------------------------
        | AKTIFKAN OUTPUT ESCAPING XML
        |--------------------------------------------------------------------------
        |
        | Secara default PhpWord menulis teks mentah (writeRaw). Dengan
        | escaping diaktifkan, karakter khusus (&, <, >, ", ') akan di-escape
        | otomatis saat menulis DOCX sehingga tidak muncul sebagai &amp; dst.
        |
        */

        \PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(true);

        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(10);

        /*
        |--------------------------------------------------------------------------
        | SECTION
        |--------------------------------------------------------------------------
        */

        $section = $phpWord->addSection([
            'marginTop' => 800,
            'marginBottom' => 800,
            'marginLeft' => 800,
            'marginRight' => 800,
        ]);

        /*
        |--------------------------------------------------------------------------
        | JUDUL
        |--------------------------------------------------------------------------
        */

        $section->addText(
            'JURNAL PRAKTIK KERJA LAPANGAN',
            [
                'bold' => true,
                'size' => 16,
            ],
            [
                'alignment' => 'center',
                'spaceAfter' => 300,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | DATA PENGGUNA
        |--------------------------------------------------------------------------
        */

        $section->addText(
            'Nama : ' . $this->cleanText($user->name),
            [
                'bold' => true,
                'size' => 11,
            ]
        );

        $section->addText(
            'Email : ' . $this->cleanText($user->email),
            [
                'size' => 11,
            ]
        );

        $section->addTextBreak(1);

        /*
        |--------------------------------------------------------------------------
        | TABEL JURNAL
        |--------------------------------------------------------------------------
        */

        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 80,
        ]);

        /*
        |--------------------------------------------------------------------------
        | HEADER TABEL
        |--------------------------------------------------------------------------
        */

        $table->addRow();

        $table->addCell(500)->addText(
            'No',
            ['bold' => true]
        );

        $table->addCell(1300)->addText(
            'Tanggal',
            ['bold' => true]
        );

        $table->addCell(1100)->addText(
            'Hari',
            ['bold' => true]
        );

        $table->addCell(2500)->addText(
            'Unit Kerja',
            ['bold' => true]
        );

        $table->addCell(2500)->addText(
            'Catatan',
            ['bold' => true]
        );

        $table->addCell(2000)->addText(
            'Dokumentasi',
            ['bold' => true]
        );

        /*
        |--------------------------------------------------------------------------
        | DATA JURNAL
        |--------------------------------------------------------------------------
        */

        foreach ($journals as $index => $journal) {

            $table->addRow();

            /*
            |--------------------------------------------------------------------------
            | Nomor
            |--------------------------------------------------------------------------
            */

            $table->addCell(500)->addText(
                $this->cleanText($index + 1)
            );

            /*
            |--------------------------------------------------------------------------
            | Tanggal
            |--------------------------------------------------------------------------
            */

            $tanggal = '-';

            if (!empty($journal->tanggal)) {
                try {
                    $tanggal = \Carbon\Carbon::parse(
                        $journal->tanggal
                    )->format('d-m-Y');
                } catch (\Throwable $e) {
                    $tanggal = '-';
                }
            }

            $table->addCell(1300)->addText(
                $this->cleanText($tanggal)
            );

            /*
            |--------------------------------------------------------------------------
            | Hari
            |--------------------------------------------------------------------------
            */

            $table->addCell(1100)->addText(
                $this->cleanText($journal->hari)
            );

            /*
            |--------------------------------------------------------------------------
            | Unit Kerja
            |--------------------------------------------------------------------------
            */

            $table->addCell(2500)->addText(
                $this->cleanText($journal->unit_kerja)
            );

            /*
            |--------------------------------------------------------------------------
            | Catatan
            |--------------------------------------------------------------------------
            */

            $table->addCell(2500)->addText(
                $this->cleanText($journal->catatan)
            );

            /*
            |--------------------------------------------------------------------------
            | Dokumentasi
            |--------------------------------------------------------------------------
            */

            $dokumentasiCell = $table->addCell(2000);

            if (!empty($journal->dokumentasi)) {
                $dokumentasiPath = storage_path('app/public/' . ltrim($journal->dokumentasi, '/'));

                if (file_exists($dokumentasiPath)) {
                    try {
                        $dokumentasiCell->addImage($dokumentasiPath, [
                            'width' => 80,
                            'height' => 80,
                            'wrappingStyle' => 'inline',
                        ]);
                    } catch (\Throwable $e) {
                        $dokumentasiCell->addText($this->cleanText('Ada'));
                    }
                } else {
                    $dokumentasiCell->addText($this->cleanText('Ada'));
                }
            } else {
                $dokumentasiCell->addText($this->cleanText('-'));
            }
        }

        /*
        |--------------------------------------------------------------------------
        | FOOTER
        |--------------------------------------------------------------------------
        */

        $section->addTextBreak(1);

        $section->addText(
            'Dokumen ini dibuat melalui Sistem Jurnal PKL.',
            [
                'italic' => true,
                'size' => 9,
            ],
            [
                'alignment' => 'center',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | SIMPAN FILE WORD
        |--------------------------------------------------------------------------
        */

        $fileName = 'jurnal-pkl-' . time() . '.docx';

        $filePath = storage_path(
            'app/' . $fileName
        );

        /*
        |--------------------------------------------------------------------------
        | BUAT FILE DOCX
        |--------------------------------------------------------------------------
        */

        $writer = IOFactory::createWriter(
            $phpWord,
            'Word2007'
        );

        $writer->save($filePath);

        /*
        |--------------------------------------------------------------------------
        | DOWNLOAD
        |--------------------------------------------------------------------------
        */

        return response()->download(
            $filePath,
            'jurnal-pkl.docx',
            [
                'Content-Type' =>
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ]
        )->deleteFileAfterSend(true);
    }
}