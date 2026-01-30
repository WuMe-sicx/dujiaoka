<?php

namespace App\Http\Controllers\Api;

use App\Models\Goods;
use App\Models\GoodsGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoodsApiController extends BaseApiController
{
    /**
     * 商品分类列表
     */
    public function groups(): JsonResponse
    {
        $groups = GoodsGroup::where('is_open', 1)
            ->orderBy('ord', 'asc')
            ->get(['id', 'gp_name']);

        return $this->success($groups);
    }

    /**
     * 商品列表
     */
    public function index(Request $request): JsonResponse
    {
        $query = Goods::where('is_open', 1)
            ->with('group:id,gp_name');

        if ($request->filled('group_id')) {
            $query->where('group_id', $request->input('group_id'));
        }

        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->where('gd_name', 'like', "%{$keyword}%")
                  ->orWhere('gd_description', 'like', "%{$keyword}%")
                  ->orWhere('gd_keywords', 'like', "%{$keyword}%");
            });
        }

        $goods = $query->orderBy('ord', 'asc')
            ->paginate($request->input('per_page', 20), [
                'id', 'group_id', 'gd_name', 'gd_description', 'gd_keywords',
                'picture', 'retail_price', 'actual_price', 'in_stock',
                'sales_volume', 'type', 'buy_limit_num',
            ]);

        return $this->paginated($goods);
    }

    /**
     * 商品详情
     */
    public function show(int $id): JsonResponse
    {
        $goods = Goods::where('is_open', 1)
            ->with('group:id,gp_name')
            ->find($id);

        if (!$goods) {
            return $this->notFound('商品不存在或已下架');
        }

        // 检查是否有访问密码（不返回密码本身）
        $hasPassword = !empty($goods->access_password);

        // 返回安全的字段集合
        $response = [
            'id' => $goods->id,
            'group_id' => $goods->group_id,
            'group' => $goods->group,
            'gd_name' => $goods->gd_name,
            'gd_description' => $goods->gd_description,
            'gd_keywords' => $goods->gd_keywords,
            'picture' => $goods->picture,
            'retail_price' => $goods->retail_price,
            'actual_price' => $goods->actual_price,
            'in_stock' => $goods->in_stock,
            'sales_volume' => $goods->sales_volume,
            'type' => $goods->type,
            'buy_limit_num' => $goods->buy_limit_num,
            'buy_prompt' => $goods->buy_prompt,
            'description' => $goods->description,
            'wholesale_price_cnf' => $goods->wholesale_price_cnf,
            'has_password' => $hasPassword,
        ];

        return $this->success($response);
    }
}
