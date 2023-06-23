<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EAccountExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function headings(): array
    {
        return [
            'EDITORIAL', 
            'TOTAL',
            'PAGOS',
            'DEVOLUCIÓN',
            'PAGAR'
        ];
    }

    public function collection()
    {
        $editoriales = \DB::table('enteditoriales')
                        ->select('editorial','total', 'total_pagos', 'total_devolucion', 'total_pendiente')
                        ->orderBy('editorial', 'asc')->get();
        return $editoriales;
    }
}
