<?php

namespace App\Exports;

use App\Models\Journal;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class JournalExport implements FromCollection, WithHeadings, WithMapping
{
    protected $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function collection()
    {
        return Journal::where('user_id', $this->userId)
            ->orderBy('tanggal', 'asc')
            ->get([
                'tanggal',
                'hari',
                'unit_kerja',
                'catatan',
                'dokumentasi',
            ]);
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Hari',
            'Unit Kerja',
            'Catatan',
            'Dokumentasi',
        ];
    }

    public function map($journal): array
    {
        $dokumentasi = '-';

        if (!empty($journal->dokumentasi)) {
            $dokumentasi = 'Ada (' . basename($journal->dokumentasi) . ')';
        }

        $tanggal = '-';

        if (!empty($journal->tanggal)) {
            try {
                $tanggal = \Carbon\Carbon::parse($journal->tanggal)->format('d-m-Y');
            } catch (\Throwable $e) {
                $tanggal = $journal->tanggal;
            }
        }

        return [
            $tanggal,
            $journal->hari ?? '-',
            $journal->unit_kerja ?? '-',
            $journal->catatan ?? '-',
            $dokumentasi,
        ];
    }
}