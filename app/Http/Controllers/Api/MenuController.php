<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Menu\CategoryRequest;
use App\Http\Requests\Api\Menu\UpdateSortingRequest;
use App\Http\Requests\Api\Menu\ChangeStatusRequest;
use App\Http\Requests\Api\Menu\DeleteRequest;
use App\Http\Requests\Api\Menu\ImageUpdateRequest;
use App\Http\Requests\Api\Menu\RemoveImageRequest;
use App\Http\Requests\Api\Menu\StoreRequest;
use App\Http\Requests\Api\Menu\UpdateRequest;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Tag;
use App\Traits\Api\CategoryTrait;
use App\Traits\Api\MenuTrait;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class MenuController extends Controller
{
    use MenuTrait, CategoryTrait;
    private $menu, $pagination, $category, $tag;
    public function __construct()
    {
        $this->menu = new Menu();
        $this->category = new Category();
        $this->tag = new Tag();
        $this->pagination = request('page_size', PAGINATE);
    }

    public function listing(Request $request)
    {
        $inputs = $request->all();
        $query = $this->menu->newQuery()->with(['categories' => function($q){
            $q->orderBy('sort_number', 'ASC')->with('tags');
        }]);
        if (!empty($inputs['search'])) {
            $query->where(function ($q) use ($inputs) {
                searchTable($q, $inputs['search'], ['title', 'text_color', 'image_style']);
            });
        }
        $menus = $query->orderBy('sort_number', 'ASC')->paginate(PAGINATE);
        return successDataResponse(GENERAL_FETCHED_MESSAGE, $menus);
    }

    public function store(StoreRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $menu = $this->menu->newInstance();
            $menu->fill($inputs);
            $menu->sort_number = $this->getNextMenuSortNumber();
            $menu->slug = getUniqueSlug($inputs['title']);
            $menu->user_id = auth()->user()->id;
            if($request->hasFile('image'))
            {
                $menu->image = uploadFile($inputs['image'], 'uploads/menus', 'menu-image');
            }
            if ($menu->save()) {
                DB::commit();
                return successDataResponse(GENERAL_SUCCESS_MESSAGE, $menu->fresh());
            }
            DB::rollback();
            return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

    public function update(UpdateRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $menu = $this->menu->newQuery()->whereId($inputs['id'])->first();
            $menu->fill($inputs);
            $menu->slug = getUniqueSlug($inputs['title']);
            if($request->hasFile('image'))
            {
                $this->deleteImage($menu->image_path);
                $menu->image = uploadFile($inputs['image'], 'uploads/menus', 'menu-image');
            }
            if ($menu->save()) {
                DB::commit();
                return successDataResponse(GENERAL_UPDATED_MESSAGE, $menu);
            }
            DB::rollback();
            return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

    public function imageUpdate(ImageUpdateRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $menu = $this->menu->newQuery()->whereId($inputs['id'])->first();
            $menu->fill($inputs);
            if ($menu->save()) {
                DB::commit();
                return successDataResponse(GENERAL_UPDATED_MESSAGE, $menu->fresh());
            }
            DB::rollback();
            return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

    public function delete(DeleteRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $menu = $this->menu->newQuery()->where('id', $inputs['id'])->first();
            $this->deleteImage($menu->image_path);
            if (!$menu->delete()) {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            if($this->category->newQuery()->whereMenuId($inputs['id'])->count() > 0)
            {
                if(!$this->category->newQuery()->whereMenuId($inputs['id'])->delete())
                {
                    DB::rollBack();
                    return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
                }
            }
            DB::commit();
            return successResponse(GENERAL_DELETED_MESSAGE);
        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

    public function changeStatus(ChangeStatusRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $menu = $this->menu->newQuery()->where('id', $inputs['id'])->first();
            if($menu->is_active)
            {
                $menu->is_active = 0;
            }else{
                $menu->is_active = 1;
            }
            if (!$menu->save()) {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            DB::commit();
            return successResponse(GENERAL_UPDATED_MESSAGE);
        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

    public function updateSorting(UpdateSortingRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            foreach($inputs['ids'] AS $key => $value){

                $menu = $this->menu->whereId($value)->first();
                $menu->sort_number = $key + 1;
                if (!$menu->save()) {
                    DB::rollback();
                    return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
                }

            }

            DB::commit();
            return successResponse(GENERAL_UPDATED_MESSAGE);
        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

    public function removeImage(RemoveImageRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $menu = $this->menu->newQuery()->whereId( $inputs['id'])->first();
            $this->deleteImage($menu->image_path);
            $menu->image = null;
            if (!$menu->save()) {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            DB::commit();
            return successDataResponse(GENERAL_UPDATED_MESSAGE, $menu->fresh());
        } catch (QueryException $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage(), ERROR_500);
        }
    }

    public function categories(CategoryRequest $request)
    {
        $inputs = $request->all();
        $query = $this->category->newQuery()->whereIn('menu_id', $inputs['menu_ids']);
        if (!empty($inputs['search'])) {
            $query->where(function ($q) use ($inputs) {
                searchTable($q, $inputs['search'], ['title']);
                searchTable($q, $inputs['search'], ['title'], 'tags');
                searchTable($q, $inputs['search'], ['title'], 'menu');
                searchTable($q, $inputs['search'], ['first_name', 'last_name', 'phone_no', 'email'], 'user');
            });
        }
        $categories = $query->orderBy('sort_number', 'ASC')->with(['menu'])->withCount(['tags'])->paginate(PAGINATE)->toArray();
        foreach($categories['data'] as $key => $cat)
        {
            $categories['data'][$key]['tags'] = $this->getNatSortedCategoryTags($cat['id']);
        }
        return successDataResponse(GENERAL_FETCHED_MESSAGE, $categories);
    }

}
