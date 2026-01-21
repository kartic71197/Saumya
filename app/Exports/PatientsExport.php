<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Log;

class PatientsExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
         \Log::info('Export Collection Raw:', $this->data->toArray());

         $mapped = collect($this->data)->map(function ($row) {
            return [
                $row['chartnumber'] ?? '',
                $row['initials'] ?? '',
                $row['ins_type'] ?? '',
                $row['provider'] ?? '',
                $row['icd'] ?? '',
                $row['address'] ?? '',
                $row['city'] ?? '',
                $row['state'] ?? '',
                $row['country'] ?? '',
                $row['pin_code'] ?? '',
                $row['location'] ?? '',
                $row['date_given'] ?? '',
                $row['product_name'] ?? '',
                $row['quantity'] ?? '',
                $row['dose'] ?? '',
                $row['frequency'] ?? '',
                $row['paid'] ?? '',
                $row['our_cost'] ?? '',
                $row['price'] ?? '',
                $row['pt_copay'] ?? '',
                $row['profit'] ?? '',
                $row['batch_number'] ?? '',
                $row['expiry_date'] ?? '',
                 ];
        });

        Log::info('Export Collection Mapped:', $mapped->toArray());

        return $mapped;
    }

    public function headings(): array
    {
        return [
            'Chart Number',
            'Initials',
            'Insurance Type',
            'Provider',
            'ICD',
            'Address',
            'City',
            'State',
            'Country',
            'Pin Code',
            'Location',
            'Date Given',
            'Product Name',
            'Quantity',
            'Dose',
            'Frequency',
            'Paid',
            'Our Cost',
            'Price',
            'Patient Copay',
            'Profit',
            'Batch Number',
            'Expiry Date',
        ];
    }
}
