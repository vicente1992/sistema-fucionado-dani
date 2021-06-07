<?php

namespace App\Exports;

use App\db_credit;
use App\db_summary;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class PaymentExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    public function __construct(int  $user_id)
    {

        $this->user_id = $user_id;
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data_user = db_credit::where('credit.id_agent', $this->user_id)
            ->where('credit.status', 'inprogress')
            ->join('users', 'credit.id_user', '=', 'users.id')
            ->select(
                'credit.*',
                'users.id as id_user',
                'users.name',
                'users.last_name'
            )
            ->get();

        foreach ($data_user as $data) {
            if (db_credit::where('id_user', $data->id_user)->where('id_agent', $this->user_id)->exists()) {

                $data->setAttribute('credit_id', $data->id);
                $data->setAttribute('amount_neto', ($data->amount_neto) + ($data->amount_neto * $data->utility));
                $data->setAttribute('positive', $data->amount_neto - (db_summary::where('id_credit', $data->id)
                    ->where('id_agent', $this->user_id)
                    ->sum('amount')));
                $data->setAttribute('payment_current', db_summary::where('id_credit', $data->id)->count());
                $data->setAttribute('remaining_payments', $data->payment_number  -  db_summary::where('id_credit', $data->id)->count());
            }
        }

        return $data_user;
    }
    public function map($row): array
    {
        return [
            $row->name . ' ' . $row->last_name,
            $row->credit_id,
            $row->amount_neto,
            $row->positive,
            $row->payment_current > 0 ?  $row->payment_current :  '0',
            $row->remaining_payments,
            $row->payment_number,

        ];
    }

    // /**
    //  * @return array
    //  */
    public function headings(): array
    {
        return [
            'Nombres',
            'Credito',
            'Valor',
            'Saldo',
            'Cuotas pagada',
            'Pagos restantes',
            'Cuotas totales',
        ];
    }
    public function registerEvents(): array
    {
        $styleArray = [
            'font' => ['bold' => true], 'alignment' => ['horizontal' => 'center']
        ];
        return [
            AfterSheet::class    => function (AfterSheet $event) use (
                $styleArray
            ) {

                $event->sheet->autoSize(true);
                $to = $event->sheet->getDelegate()->getHighestRowAndColumn();
                $rows = $event->sheet->getDelegate()->toArray();
                $cellRange = 'A1:G1';
                $event->sheet->getStyle($cellRange)->ApplyFromArray($styleArray);
                $event->sheet->getStyle('A1')->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);
                $event->sheet->getStyle('A1:' . $to['column'] . $to['row'])->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);
            },
        ];
    }
}
