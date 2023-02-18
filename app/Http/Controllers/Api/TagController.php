<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Tag\DeleteRequest;
use App\Http\Requests\Api\Tag\StoreRequest;
use App\Http\Requests\Api\Tag\UpdateRequest;
use App\Models\Category;
use App\Models\ProductTag;
use App\Models\Tag;
use App\Traits\Api\TagTrait;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TagController extends Controller
{
    use TagTrait;
    private $tag, $category, $productTag;
    public function __construct()
    {
        $this->category = new Category();
        $this->tag = new Tag();
        $this->productTag = new ProductTag();
    }

    public function store(StoreRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $tag = $this->tag->newInstance();
            $tag->fill($inputs);
            $code = $code = Str::random(3);
            $tag->slug = preg_replace('/[^a-z0-9]+/i', '-', trim(strtolower($inputs['title']))).'-'.$code;
            $tag->user_id = auth()->user()->id;
            if ($tag->save()) {
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

    public function delete(DeleteRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $tag = $this->tag->newQuery()->where('id', $inputs['id'])->first();
            if (!$tag->delete()) {
                DB::rollback();
                return errorResponse(GENERAL_ERROR_MESSAGE, ERROR_400);
            }
            if (!$this->isTagDeletedFromProductTags($inputs['id'])) {
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
}
