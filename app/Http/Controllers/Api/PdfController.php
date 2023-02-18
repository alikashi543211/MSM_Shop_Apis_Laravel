<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Pdf\DownloadPdfRequest;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Doctrine\DBAL\Query\QueryException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Milon\Barcode\DNS1D;

class PdfController extends Controller
{
    private $product;
    public function __construct()
    {
        $this->product = new Product();
    }

    public function downloadPdf(DownloadPdfRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            // Subtotal Calculation
            $subTotalBackend = 0;
            if(isset($inputs['total_price']))
            {
                $subTotalBackend = $subTotalBackend + $inputs['total_price'];
            }
            if(isset($inputs['total_discount']))
            {
                $subTotalBackend = $subTotalBackend + $inputs['total_discount'];
            }
            if(isset($inputs['delivery_fee']))
            {
                $subTotalBackend = $subTotalBackend + $inputs['delivery_fee'];
            }
            $subTotalBackend = twoDecimal($subTotalBackend);
            $landed_cost_overall = collect($inputs['items'])->sum('landed_cost');
            if($landed_cost_overall > 0)
            {
                foreach($inputs['items'] as $key => $productItem)
                {
                    $inputs['items'][$key]['item_price_backend'] = twoDecimal($productItem['landed_cost'] * $productItem['quantity']);
                }
            }else{
                foreach($inputs['items'] as $key => $productItem)
                {
                    $inputs['items'][$key]['item_price_backend'] = twoDecimal($productItem['price']);
                }
            }
            set_time_limit(600);
            $barcode = 'data:image/png;base64,'. DNS1D::getBarcodePNG($inputs['business_reference_id'], 'C128');
            $barcode_text = str_replace("_", '', $inputs['business_reference_id']);
            $name = $this->getPdfFileName('shopping-cart');
            $pdf = Pdf::loadView('pdf.shopping_cart', get_defined_vars())->setPaper('letter', 'portrait')->save($name);
            return successDataResponse(GENERAL_FETCHED_MESSAGE, ['link' => url($name)]);
        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

    public function testDownloadPdf(Request $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            set_time_limit(600);
            $barcode = 'data:image/png;base64,'. DNS1D::getBarcodePNG("4445645656", 'C39+');
            $name = $this->getPdfFileName('shopping-cart');
            $pdf = Pdf::loadView('pdf.shopping_cart_design', get_defined_vars())->setPaper('letter', 'portrait');
            return $pdf->stream($name);
            return successDataResponse(GENERAL_FETCHED_MESSAGE, ['link' => url($name)]);
        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }
}
