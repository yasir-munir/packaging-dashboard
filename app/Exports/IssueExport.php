<?php

namespace App\Exports;
use DB;
use App\Models\Role;
use App\Models\User;
use App\Models\Sale;
use App\Models\SaleReturn;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class IssueExport implements FromArray, WithHeadings, ShouldAutoSize, WithEvents
{
    /**
     * @return \Illuminate\Support\Collection
     */
    function array(): array
    {
        $Role = Auth::user()->roles()->first();
        $ShowRecord = Role::findOrFail($Role->id)->inRole('record_view');

        $users = User::where('deleted_at', '=', null)
            ->orderBy('id', 'DESC')
            ->get();
        
        $data = [];
        foreach ($users as $user){

            $productDetail['username'] = $user->firstname." ".$user->lastname;

            $productIDs = DB::table('sales')
            ->where(function ($query) use ($ShowRecord) {
                if (!$ShowRecord) {
                    return $query->where('sales.user_id', '=', Auth::user()->id);
                }
            })
            ->where('sales.deleted_at', '=', null)
            ->join('sale_details', 'sales.id', '=', 'sale_details.sale_id')
            ->where('sales.user_id', $user->id)
            ->select('sale_details.product_id');

            $productIDs = $productIDs->distinct('sale_details.product_id')
                ->orderBy('sale_details.product_id', 'asc') 
                ->get();
                
            
            foreach ($productIDs as $productID){
                
                $productDetailName = DB::table('products')->where('products.id', '=', $productID->product_id)->get('name');
                $productDetail['product_name'] = $productDetailName[0];
                
                $productDetail['issued_qty'] = DB::table('sales')
                    ->where('sales.user_id', '=', $user->id)
                    ->join('sale_details', 'sales.id', '=', 'sale_details.sale_id')
                    ->where('sale_details.product_id', '=', $productID->product_id)
                    ->sum('sale_details.quantity');
                
                $productDetail['returned_qty'] = DB::table('sale_returns')
                    ->where('sale_returns.sale_user_id', '=', $user->id)
                    ->join('sale_return_details', 'sale_returns.id', '=', 'sale_return_details.sale_return_id')
                    ->where('sale_return_details.product_id', $productID->product_id)
                    ->sum('sale_return_details.quantity');
                $productDetail['dead_qty'] = DB::table('adjustments')
                    ->where('adjustments.adj_user_id', '=', $user->id)
                    ->join('adjustment_details', 'adjustments.id', '=', 'adjustment_details.adjustment_id')
                    ->where('adjustment_details.product_id', $productID->product_id)
                    ->where('adjustment_details.type', '=', 'dead')
                    ->sum('adjustment_details.quantity');        
                $productDetail['in_hand_qty'] = $productDetail['issued_qty'] - $productDetail['returned_qty'] - $productDetail['dead_qty'];

                $data[] = $productDetail;
            
            }
        }
        return $data;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $cellRange = 'A1:F1'; // All headers
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(14);

                $styleArray = [
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                            'color' => ['argb' => 'FFFF0000'],
                        ],
                    ],

                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                    ],
                ];

            },
        ];

    }

    public function headings(): array
    {
        return [
            'Employee',
            'Items',
            'Issued',
            'Return',
            'Dead',
            'In Hand',
        ];
    }
}
