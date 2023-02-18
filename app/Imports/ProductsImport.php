<?php

namespace App\Imports;

use App\Models\Merchant;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductMailBox;
use App\Models\ProductMerchant;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductsImport implements ToCollection,WithHeadingRow
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        $responseImport['result'] = true;
        $responseImport['message'] = GENERAL_SUCCESS_MESSAGE;
        // Valid Excel Keys
        $validHeadingKeys = $this->getValidExcelKeys();
        // Validation
        if(!$this->isExcelHeadingKeysValidated($collection->first(), $validHeadingKeys))
        {
            $this->setResponseErrorMessage($responseImport);
            return false;
        }

        // Fetch Product Mailboxes and Product Merchant Headings
        $product_mailbox_headings = [];
        $product_merchant_headings = [];
        $this->getProductMailBoxesHeading($validHeadingKeys, $product_mailbox_headings);
        $this->getProductMerchantHeading($validHeadingKeys, $product_merchant_headings);

        foreach($collection as $row)
        {
            $row = $row->toArray();
            // $this->validateDateFieldsInExcel($row);

            if(!isset($row['title']))
            {
                continue;
            }

            $product_columns = [];
            $product_attributes = [];
            $product_mailboxes = [];
            $product_merchants = [];
            $createdProductId = null;

            // dd($row['publish_date'], gettype($row['publish_date']));
            if(!$this->isDatesInFileAreValid($row))
            {

            }
            if(!$this->isPubslishedAtValid($row))
            {
                $this->setResponseErrorMessage($responseImport);
                return false;
            }
            if(!$this->isExpiredAtValid($row))
            {
                $this->setResponseErrorMessage($responseImport);
                return false;
            }

            // Make Data For Import
            $this->makeProductColumns($row, $product_columns);

            $this->makeProductAttributes($row, $product_attributes);

            $this->makeProductMailBoxes($row, $product_mailboxes, $product_mailbox_headings);

            $this->makeProductMerchants($row, $product_merchants, $product_merchant_headings);


            if(!$this->isProductInfoSaved($product_columns, $createdProductId))
            {
                $this->setResponseErrorMessage($responseImport);
                return false;
            }

            if(!$this->isProductAttributesSaved($product_attributes, $createdProductId))
            {
                $this->setResponseErrorMessage($responseImport);
                return false;
            }
            if(!$this->isProductMailboxesSaved($product_mailboxes, $createdProductId))
            {
                $this->setResponseErrorMessage($responseImport);
                return false;
            }
            if(!$this->isProductMerchantsSaved($product_merchants, $createdProductId))
            {
                $this->setResponseErrorMessage($responseImport);
                return false;
            }
        }
        $this->setResponseSuccessrMessage($responseImport);
        return true;
    }

    private function isDatesInFileAreValid(&$row)
    {
        dd($row['publish_date']);
    }

    private function validateDateFieldsInExcel(&$row)
    {

        if (DateTime::createFromFormat('Y-m-d H:i:s', $row['publish_date']) !== false) {
            dd("Value is Date");
        }
        dd("Value is Not Date", $row['publish_date']);
    }

    private function setResponseErrorMessage(&$responseImport)
    {
        $responseImport['result'] = false;
        $responseImport['message'] = GENERAL_EXCEL_IMPORT_ERROR;
        Session::put('responseImport', $responseImport);
    }

    private function setResponseSuccessrMessage(&$responseImport)
    {
        $responseImport['result'] = true;
        $responseImport['message'] = GENERAL_SUCCESS_MESSAGE;
        Session::put('responseImport', $responseImport);
    }

    private function isProductMerchantsSaved(&$product_merchants, &$createdProductId)
    {

        if(isset($product_merchants) && count($product_merchants) > 0)
        {
            foreach($product_merchants as $key => $value)
            {
                if(!isset($value['merchant_id']))
                {
                    continue;
                }
                $value['shipping'] = $value['shipping_cost'];
                $productMerchant = new ProductMerchant();
                $productMerchant->fill($value);
                $productMerchant->import_taxes = twoDecimal($value['duty'] + $value['wharfage']);
                $productMerchant->shipping_charges = twoDecimal($value['shipping'] + $value['fuel_adjustment'] + $value['insurance']);
                $productMerchant->product_id = $createdProductId;
                $productMerchant->sort_number = $this->getNextProductMerchantSortNumber($createdProductId);
                if(!$productMerchant->save())
                {
                    return false;
                }

            }
        }
        return true;
    }

    private function getNextProductMerchantSortNumber(&$createdProductId)
    {
        $sort_number = 1;
        if(ProductMerchant::whereProductId($createdProductId)->count() > 0)
        {
            $sort_number = ProductMerchant::whereProductId($createdProductId)->orderBy('sort_number', 'desc')->value('sort_number') + 1;
        }
        return $sort_number;
    }

    private function isProductMailboxesSaved(&$product_mailboxes, &$createdProductId)
    {
        if(isset($product_mailboxes) && count($product_mailboxes) > 0)
        {
            foreach($product_mailboxes as $key => $value)
            {
                $productMailbox = new ProductMailBox();
                $productMailbox->fill($value);
                $productMailbox->product_id = $createdProductId;
                if(!$productMailbox->save())
                {
                    return false;
                }
                if(!$this->isSavedCostAfterDiscount($productMailbox))
                {
                    return false;
                }
            }
        }
        return true;
    }

    private function isSavedCostAfterDiscount($productMailbox)
    {
        if($productMailbox->discount_type == PERCENTAGE_DISCOUNT_TYPE)
        {
            $discount_amount = twoDecimal(($productMailbox->landed_cost * $productMailbox->discount ) / 100);
            $productMailbox->cost_after_discount = twoDecimal($productMailbox->landed_cost - $discount_amount);
            $productMailbox->discount_amount = $discount_amount;
            if(!$productMailbox->save())
            {
                return false;
            }
        }elseif($productMailbox->discount_type == FLAT_DISCOUNT_TYPE)
        {
            $productMailbox->cost_after_discount = twoDecimal($productMailbox->landed_cost - $productMailbox->discount);
            $productMailbox->discount_amount = $productMailbox->discount;
            if(!$productMailbox->save())
            {
                return false;
            }
        }
        return true;

    }

    private function isProductAttributesSaved(&$product_attributes, &$createdProductId)
    {
        foreach($product_attributes as $newAttribute)
        {
            $attribute = new ProductAttribute();
            $attribute->fill($newAttribute);
            $attribute->product_id = $createdProductId;
            if(!$attribute->save())
            {
                return false;
            }
        }
        return true;
    }

    private function isProductInfoSaved(&$product_columns, &$createdProductId)
    {
        $product = new Product();
        $product->fill($product_columns);
        $product->slug = getUniqueSlug($product_columns['title']);
        $product->user_id = auth()->user()->id;
        if(!$product->save())
        {
            return false;
        }
        $createdProductId = $product->id;
        return true;
    }

    private function makeProductMerchants(&$row, &$product_merchants, &$product_merchant_headings)
    {
        for ($i=0; $i < count(explode('-', $row['merchant_name'])); $i++) {
            foreach($product_merchant_headings as $heading)
            {
                if($heading == 'merchant_name')
                {
                    $merchant = Merchant::whereName($row['merchant_name'])->first();
                    if($merchant)
                    {
                        $product_merchants[$i]['merchant_id'] = $merchant->id;
                        continue;
                    }
                    $product_merchants[$i]['merchant_id'] = null;
                }
                $product_merchants[$i][explode('merchant_',$heading)[1]] = explode(',', $row[$heading])[$i];
            }
        }
    }

    private function makeProductMailboxes(&$row, &$product_mailboxes, &$product_mailbox_headings)
    {
        for ($i=0; $i < count(explode('-', $row['mb_sku'])); $i++) {
            foreach($product_mailbox_headings as $heading)
            {
                if($heading == 'mb_sku')
                {
                    $sku = explode('-', $row[$heading])[$i];
                    $product_mailboxes[$i][explode('mb_',$heading)[1]]=$sku;
                }else{
                    $product_mailboxes[$i][explode('mb_',$heading)[1]] = explode(',', $row[$heading])[$i];
                }
            }
        }
    }

    private function isPubslishedAtValid(&$row)
    {

        if(isset($row['publish_date']))
        {
            if (DateTime::createFromFormat('Y-m-d H:i:s', $row['publish_date']) !== false) {
                // it's a date
                $publishedAt = strtotime($row['publish_date']);
                $nowAt = strtotime(date('Y-m-d'));
                if($publishedAt <= $nowAt)
                {
                    return true;
                }
            }else{
                // Value is not date
                return false;
            }
        }
        return true;
    }

    private function isExpiredAtValid(&$row)
    {
        dd($row['expire_date']);
        if(isset($row['expire_date']))
        {
            $expiredAt = strtotime($row['expire_date']);
            $nowAt = strtotime(date('Y-m-d'));
            if($expiredAt > $nowAt)
            {
                return true;
            }
            return false;
        }
        return true;
    }

    private function makeProductColumns(&$row, &$product_columns)
    {
        // $product_keys = [
        //     'title',
        //     'description',
        //     'published_at',
        //     'expired_at',
        //     'stock',
        //     'active',
        //     'stock',
        //     'stock',
        // ];
        $product_columns['title'] = $row['title'];
        $product_columns['description'] = $row['description'];
        $product_columns['published_at'] = date("Y-m-d", strtotime($row['publish_date']));
        $product_columns['expired_at'] = date("Y-m-d", strtotime($row['expire_date']));
        $product_columns['in_stock'] = $row['stock'];
        if($row['active'] == true || $row['active'] == "TRUE")
        {
            $product_columns['is_active'] = true;
        }else{
            $product_columns['is_active'] = false;
        }
        if($row['show_buying_options'] == true || $row['show_buying_options'] == "TRUE")
        {
            $product_columns['show_buying_options'] = true;
        }else{
            $product_columns['show_buying_options'] = false;
        }
        if($row['show_buying_options'] == true || $row['show_buying_options'] == "TRUE")
        {
            $product_columns['is_buy_now'] = true;
        }else{
            $product_columns['is_buy_now'] = false;
        }

    }

    private function makeProductAttributes(&$row, &$product_attributes)
    {
        if(isset($row['attributes']))
        {
            $attributes = $row['attributes'];
            $attributes = explode(',', $attributes);
            $index = 0;
            foreach($attributes as $key => $attribute)
            {
                $explodeAttribute = explode("=", $attribute);
                $product_attributes[$index]['key'] = $explodeAttribute[0];
                $product_attributes[$index]['value'] = $explodeAttribute[1];
                $index++;
            }
        }
    }

    private function getProductMailBoxesHeading($validHeadingKeys, &$product_mailbox_headings)
    {
        foreach($validHeadingKeys as $input_string)
        {
            $sub = "mb_";
            if (strpos($input_string, $sub) !== false)
            {
                $product_mailbox_headings[] = $input_string;
            }
        }
    }

    private function getProductMerchantHeading($validHeadingKeys, &$product_merchant_headings)
    {
        foreach($validHeadingKeys as $input_string)
        {
            $sub = "merchant_";
            if (strpos($input_string, $sub) !== false)
            {
                $product_merchant_headings[] = $input_string;
            }
        }
    }

    private function isExcelHeadingKeysValidated($firstRow, $validHeadingKeys)
    {

        if($firstRow)
        {
            $givenHeadingKeys = array_keys($firstRow->toArray());
            // dd("Ok", $givenHeadingKeys);
            $intersectedHeadingKeys = array_intersect($validHeadingKeys, $givenHeadingKeys);
            $diff = array_diff($validHeadingKeys, $intersectedHeadingKeys);
            if(count($diff) == 0)
            {
                return true;
            }
            return false;
        }
        return false;
    }

    private function getValidExcelKeys()
    {
        return [
                "title",
                "description",
                "publish_date",
                "expire_date",
                "stock",
                "active",
                "show_buying_options",
                "show_add_to_cart",
                "attributes",
                "mb_sku",
                "mb_location",
                "mb_landed_cost",
                "mb_stock",
                "mb_discount_type",
                "mb_discount_value",
                "merchant_name",
                "merchant_link",
                "merchant_retail_cost",
                "merchant_duty",
                "merchant_wharfage",
                "merchant_shipping_cost",
                "merchant_fuel_adjustment",
                "merchant_insurance",
                "merchant_estimated_landed_cost",
        ];
    }

    public function headingRow(): int
    {
        return 1;
    }

}
