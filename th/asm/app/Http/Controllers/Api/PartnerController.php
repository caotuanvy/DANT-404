<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PartnerController extends Controller
{

   public function index(Request $request)
{
    try {
        $query = Partner::select(
            'id',
            'Ten_doi_tac as tenDoiTac',
            'Thuong_hieu_doi_tac as logo',
            'trang_thai'
        )
        ->withCount('products');

        if ($request->has('search') && $request->search != '') {
            $search = $request->input('search');
            $query->where('Ten_doi_tac', 'LIKE', '%' . $search . '%');
        }

        $partners = $query->get();
        return response()->json($partners, 200);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Không thể tải dữ liệu đối tác.'], 500);
    }
}


    public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'tenDoiTac' => 'required|string|unique:doitac,Ten_doi_tac|max:255',
        'logo' => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 400);
    }

    try {
        $partner = Partner::create([
            'Ten_doi_tac' => $request->tenDoiTac,
            'Thuong_hieu_doi_tac' => $request->logo,
        ]);

        return response()->json([
            'message' => 'Thêm đối tác thành công.',
            'partner' => $partner
        ], 201);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Lỗi khi thêm đối tác.',
            'message' => $e->getMessage(),
            'line' => $e->getLine()
        ], 500);
    }
}





   public function show($id)
{
    try {
        $partner = Partner::select(
            'id',
            'Ten_doi_tac as tenDoiTac',
            'Thuong_hieu_doi_tac as logo'
        )->find($id);

        if (!$partner) {
            return response()->json(['error' => 'Không tìm thấy đối tác.'], 404);
        }

        return response()->json($partner, 200);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Lỗi khi tải dữ liệu đối tác.'], 500);
    }
}

    public function update(Request $request, $id)
    {
        try {

            $partner = Partner::find($id);
            if (!$partner) {
                return response()->json(['error' => 'Không tìm thấy đối tác.'], 404);
            }


            $validator = Validator::make($request->all(), [
                'tenDoiTac' => 'required|string|max:255|unique:doitac,Ten_doi_tac,' . $partner->id,
                'logo' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $partner->update([
                'Ten_doi_tac' => $request->input('tenDoiTac'),
                'Thuong_hieu_doi_tac' => $request->input('logo', $partner->Thuong_hieu_doi_tac),
            ]);

            return response()->json(['message' => 'Cập nhật đối tác thành công.', 'partner' => $partner], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Lỗi khi cập nhật đối tác.', 'message' => $e->getMessage()], 500);
        }
    }

   public function destroy($id)
{
    $partner = Partner::find($id);

    if (!$partner) {
        return response()->json(['message' => 'Đối tác không tồn tại'], 404);
    }

    $partner->trang_thai = 0;
    $partner->save();

    return response()->json(['message' => 'Đối tác đã bị vô hiệu hóa thành công']);
}
public function updateStatus($id)
{
    $partner = Partner::findOrFail($id);
    $partner->trang_thai = !$partner->trang_thai;
    $partner->save();

    return response()->json([
        'success' => true,
        'message' => 'Cập nhật trạng thái thành công!',
        'partner' => $partner
    ]);
}
public function getActivePartners()
    {
        try {

            $partners = Partner::where('trang_thai', 1)
                               ->select('id', 'Ten_doi_tac', 'Thuong_hieu_doi_tac')
                               ->get();
            return response()->json($partners, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Không thể tải danh sách đối tác.'], 500);
        }
    }
}
