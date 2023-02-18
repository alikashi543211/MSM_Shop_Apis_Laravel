<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Customer\Menu\DetailRequest;
use App\Models\Menu;
use App\Traits\Api\MenuTrait;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    use MenuTrait;
    private $menu, $pagination;
    public function __construct()
    {
        $this->menu = new Menu();
        $this->pagination = request('page_size', PAGINATE);
    }

    public function listing(Request $request)
    {
        $inputs = $request->all();
        $query = $this->menu->newQuery()->whereIsActive(1)->withCount(['active_products as products_count']);
        if (!empty($inputs['search'])) {
            $query->where(function ($q) use ($inputs) {
                searchTable($q, $inputs['search'], ['title', 'text_color', 'image_style']);
            });
        }
        $menus = $query->orderBy('sort_number', 'ASC')->paginate(PAGINATE);
        return successDataResponse(GENERAL_FETCHED_MESSAGE, $menus);
    }

    public function detail(DetailRequest $request)
    {
        $inputs = $request->all();
        $query = $this->menu->newQuery();
        if(!empty($inputs['id']))
        {
            $query->whereId($inputs['id']);
        }elseif(!empty($inputs['slug']))
        {
            $query->whereSlug($inputs['slug']);
        }

        $menu = $query->first();
        return successDataResponse(GENERAL_FETCHED_MESSAGE, $menu);
    }
}
