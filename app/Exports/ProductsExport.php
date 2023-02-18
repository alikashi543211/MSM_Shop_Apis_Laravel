<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ProductsExport implements FromView, WithTitle, WithHeadings, WithEvents, WithColumnFormatting, WithMapping
{
    protected $data;

    public function map($invoice): array
    {
        return [
            Date::dateTimeToExcel($invoice->published_at),
            Date::dateTimeToExcel($invoice->expired_at),
        ];
    }

    /**
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'D' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }

    public function headings() :array
    {
        return [
            'Title',
            'Description',
            'Publish Date',
            'Expire Date',
            'Stock',
            'Active',
            'Show Buying Options',
            'Show Add to Cart',
            'Attributes',
            'MB SKU',
            'MB Location',
            'MB Landed Cost',
            'MB Stock',
            'MB Discount Type',
            'MB Discount Value',
            'Merchant Name',
            'Merchant Link',
            'Merchant Retail Cost',
            'Merchant Duty',
            'Merchant Wharfage',
            'Merchant Shipping Cost',
            'Merchant Fuel Adjustment',
            'Merchant Insurance',
            'Merchant Estimated Landed Cost',
        ];

    }

    function __construct($data)
    {
        $this->data = $data;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {

                $event->sheet->getDelegate()->getStyle('A1:X1')
                                ->getFont()
                                ->setBold(true);

            },
        ];
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $products = $this->data;

        foreach($products as $key => $item)
        {
            $this->customizeProductFormat($item);
            $this->customizeProductAttributesFormat($item);
            $this->customizeProductMailBoxesFormat($item);
            $this->customizeProductMerchantsFormat($item);
        }

        return view('exports.products', get_defined_vars());
    }

    private function customizeProductMailBoxesFormat(&$item)
    {
        $item->mb_sku = null;
        $item->mb_discount_value = null;
        $item->mb_discount_type = null;
        $item->mb_stock = null;
        $item->mb_landed_cost = null;
        $item->mb_location = null;
        if( count($item->productMailBoxes) > 0 )
        {
            $item->mb_discount_value = implode(",", collect($item->productMailBoxes)->pluck('discount')->toArray());
            $item->mb_discount_type = implode(",", collect($item->productMailBoxes)->pluck('discount_type')->toArray());
            $item->mb_stock = implode(",", collect($item->productMailBoxes)->pluck('stock')->toArray());
            $item->mb_landed_cost = implode(",", collect($item->productMailBoxes)->pluck('landed_cost')->toArray());
            $item->mb_location = implode(",", collect($item->productMailBoxes)->pluck('location')->toArray());
            $item->mb_sku = implode(",", collect($item->productMailBoxes)->pluck('sku')->toArray());
        }
        return true;
    }

    private function customizeProductMerchantsFormat(&$item)
    {
        $item->merchant_name = null;
        $item->merchant_link = null;
        $item->merchant_retail_cost = null;
        $item->merchant_duty = null;
        $item->merchant_wharfage = null;
        $item->merchant_shipping_cost = null;
        $item->merchant_fuel_adjustments = null;
        $item->merchant_insurance = null;
        $item->merchant_estimated_landed_cost = null;
        if( count($item->productMerchants) > 0 )
        {
            $item->merchant_name = implode(",", collect(collect($item->productMerchants)->pluck('merchant')->toArray())->pluck('name')->toArray());
            $item->merchant_link = implode(",", collect($item->productMerchants)->pluck('link')->toArray());
            $item->merchant_retail_cost = implode(",", collect($item->productMerchants)->pluck('retail_cost')->toArray());
            $item->merchant_duty = implode(",", collect($item->productMerchants)->pluck('duty')->toArray());
            $item->merchant_wharfage = implode(",", collect($item->productMerchants)->pluck('wharfage')->toArray());
            $item->merchant_shipping_cost = implode(",", collect($item->productMerchants)->pluck('shipping_charges')->toArray());
            $item->merchant_fuel_adjustments = implode(",", collect($item->productMerchants)->pluck('fuel_adjustment')->toArray());
            $item->merchant_insurance = implode(",", collect($item->productMerchants)->pluck('insurance')->toArray());
            $item->merchant_estimated_landed_cost = implode(",", collect($item->productMerchants)->pluck('estimated_landed_cost')->toArray());
        }
        return true;
    }

    private function customizeProductFormat(&$item)
    {
        // if(isset($item->expired_at))
        // {
        //     $item->expired_at = Date::dateTimeToExcel($item->expired_at);
        // }
        // if(isset($item->expired_at))
        // {
        //     $item->expired_at = Date::dateTimeToExcel($item->expired_at);
        // }
    }


    private function customizeProductAttributesFormat(&$item)
    {
        $product_attributes = [];
        $item->product_attributes_formatted = "";
        if( count($item->productAttributes) > 0 )
        {
            foreach($item->productAttributes as $key => $productAttribute)
            {
                array_push($product_attributes, implode("=", $productAttribute->attribute));
            }
            if( count($product_attributes) > 0 )
            {
                $item->product_attributes_formatted = implode(",", $product_attributes);
            }
        }
    }

    public function title(): string
    {
        return 'Products';
    }
}
