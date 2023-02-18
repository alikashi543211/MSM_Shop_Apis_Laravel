<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Category\DeleteRequest;
use App\Http\Requests\Api\Category\ListingRequest;
use App\Http\Requests\Api\Category\StoreRequest;
use App\Http\Requests\Api\Category\UpdateRequest;
use App\Http\Requests\Api\Category\UpdateSortingRequest;
use App\Jobs\SendMailJob;
use App\Models\Category;
use App\Models\ProductTag;
use App\Traits\Api\CategoryTrait;
use App\Models\Tag;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    use CategoryTrait;
    private $tag, $category, $productTag;

    public function __construct()
    {
        $this->category = new Category();
        $this->tag = new Tag();
        $this->productTag = new ProductTag();
    }

    public function listing(ListingRequest $request)
    {
        $inputs = $request->all();
        $query = $this->category->newQuery();
        if(!empty($inputs['is_dropdown']))
        {
            $query->has('tags');
        }
        if(!empty($inputs['menu_id']))
        {
            $query->whereMenuId($inputs['menu_id'])->orderBy('sort_number', 'ASC');
        }
        if (!empty($inputs['search'])) {
            $query->where(function ($q) use ($inputs) {
                searchTable($q, $inputs['search'], ['title']);
                searchTable($q, $inputs['search'], ['title'], 'tags');
                searchTable($q, $inputs['search'], ['title'], 'menu');
                searchTable($q, $inputs['search'], ['first_name', 'last_name', 'phone_no', 'email'], 'user');
            });
        }
        $categories = $query->with(['menu'])->withCount(['tags'])->paginate(PAGINATE)->toArray();
        foreach($categories['data'] as $key => $cat)
        {
            $categories['data'][$key]['tags'] = $this->getNatSortedCategoryTags($cat['id']);
        }
        return successDataResponse(GENERAL_FETCHED_MESSAGE, $categories);
    }

    public function store(StoreRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();

            $category = $this->category->newInstance();
            $category->fill($inputs);
            $category->sort_number = $this->getNextCategorySortNumber($inputs['menu_id']);
            $category->slug = getUniqueSlug($inputs['title']);
            $category->user_id = auth()->user()->id;
            if ($category->save()) {
                DB::commit();
                return successResponse(GENERAL_SUCCESS_MESSAGE);
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
            $category = $this->category->newQuery()->whereId($inputs['id'])->first();
            $oldMenuId = $category->menu_id;
            $category->fill($inputs);
            if($oldMenuId != $inputs['menu_id'])
            {
                $category->sort_number = $this->getNextCategorySortNumber($inputs['menu_id']);
            }
            $category->slug = getUniqueSlug($inputs['title']);
            if ($category->save()) {
                DB::commit();
                return successResponse(GENERAL_UPDATED_MESSAGE);
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

            $category = $this->category->newQuery()->where('id', $inputs['id'])->first();

            if($this->tag->whereCategoryId($inputs['id'])->count() > 0)
            {
                if(!$this->tag->whereCategoryId($inputs['id'])->delete())
                {
                    DB::rollback();
                    return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
                }
            }

            if($this->productTag->whereCategoryId($inputs['id'])->count() > 0)
            {
                if(!$this->productTag->whereCategoryId($inputs['id'])->delete())
                {
                    DB::rollback();
                    return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
                }
            }

            if (!$category->delete()) {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
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

    public function updateSorting(UpdateSortingRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            if(!$this->validateSizeOfIds($inputs)[0])
            {
                DB::rollback();
                return errorResponse("The ids must contain ". $this->validateSizeOfIds($inputs)[1] ." items.", ERROR_400);
            }
            foreach($inputs['ids'] AS $key => $value){

                $category = $this->category->whereId($value)->first();
                $category->sort_number = $key + 1;
                if (!$category->save()) {
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

}
