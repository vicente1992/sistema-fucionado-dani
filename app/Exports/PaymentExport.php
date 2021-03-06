<?php

namespace App\Exports;

use App\db_credit;
use App\db_summary;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class PaymentExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithColumnWidths,
    WithEvents
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

                // Coutas atrasads
                $amount_summary = db_summary::where('id_credit', $data->id)->sum('amount');
                $days_crea = count_date($data->created_at);
                $data->total = floatval($data->utility_amount + $data->amount_neto);
                $data->days_crea = $days_crea;
                $quote = $data->total  / floatval($data->payment_number);
                $quote = $data->total  / floatval($data->payment_number);
                $pay_res = (floatval($days_crea * $quote)  -  $amount_summary);
                $days_rest = floatval($pay_res / $quote - 1);
                $data->days_rest =  round($days_rest) > 0 ? round($days_rest) : 0;
                if ($data->days_rest < 12) {
                    $data->estado = 'BUENO';
                } else if ($data->days_rest >= 12 && $data->days_rest < 30) {
                    $data->estado = 'REGULAR';
                } else if ($data->days_rest > 30) {
                    $data->estado = 'MALO';
                }
            }
        }

        return $data_user;
    }


    public function map($row): array
    {
        return [
            $row->created_at,
            $row->name . ' ' . $row->last_name,
            $row->credit_id,
            $row->amount_neto,
            $row->positive,
            $row->payment_current > 0 ?  $row->payment_current :  '0',
            $row->remaining_payments,
            $row->payment_number,
            $row->estado,
            // $row->days_rest < 12 ? 'BUENO' , $row->days_rest >= 12  && $row->days_rest < 30 ? 'REGULAR' : '', $row->days_rest >= 30 ? 'MALO' : '',

            //  $client->days_rest >= 12 && $client->days_rest <30

        ];
    }

    // /**
    //  * @return array
    //  */
    public function headings(): array
    {
        return [
            'Fecha',
            'Nombres',
            'Credito',
            'Valor',
            'Saldo',
            'Cuotas pagada',
            'Pagos restantes',
            'Cuotas totales',
            'Estado',
        ];
    }
    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 30,
            'C' => 12,
            'D' => 12,
            'E' => 18,
            'F' => 18,
            'G' => 18,
            'H' => 18,
            'I' => 18,
        ];
    }
    public function registerEvents(): array
    {
        $styleArray = [
            'font' => ['bold' => true], 'alignment' => ['horizontal' => 'center'],
            'fill' => ['fillType' => 'solid', 'color' => array('rgb' => 'EBFA1B')],
        ];
        $styleArray4 = [
            'alignment' => ['horizontal' => 'center']
        ];
        return [
            AfterSheet::class    => function (AfterSheet $event) use (
                $styleArray,
                $styleArray4
            ) {

                // $event->sheet->autoSize(true);
                $to = $event->sheet->getDelegate()->getHighestRowAndColumn();
                $rows = $event->sheet->getDelegate()->toArray();
                $cellRange = 'A1:I1';
                $event->sheet->getStyle($cellRange)->ApplyFromArray($styleArray);
                $event->sheet->getStyle('A:I')->ApplyFromArray($styleArray4);
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

                $column_b = '';
                $column_c = '';
                $column_d = 0;
                $column_e = 0;
                $column_f = '';
                $column_g = '';
                $column_h = '';
                $column_i = '';
                foreach ($rows as  $row) {
                    if (is_numeric($row[3])) {
                        $column_d = $column_d + $row[3];
                    }
                    if (is_numeric($row[4])) {
                        $column_e = $column_e + $row[4];
                    }
                }

                $event->sheet->appendRows(
                    array(
                        array(
                            'Total',
                            "$column_b",
                            "$column_c",
                            "$column_d",
                            "$column_e",
                            " $column_f",
                            "$column_g",
                            "$column_h",
                            $column_i
                        ),
                    ),
                    $event
                );
                $total_rows = count($rows) + 1;
                $range = 'A' . $total_rows . ':' . 'I' . $total_rows;
                $event->sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                    'fill' => ['fillType' => 'solid'],
                ]);
            },


        ];
    }
}
