<?php

namespace App\Http\Controllers\Api;

use App\Models\DiaChi;
use App\Models\User; // Hoặc tên model người dùng thực tế của bạn
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Controller;
// use Illuminate\Support\Facades\DB; // <-- Không cần dòng này nữa nếu không dùng transaction cho mac_dinh
use Illuminate\Support\Facades\Log;

class DiaChiController extends Controller
{
    /**
     * Lấy danh sách các địa chỉ.
     * Vẫn giữ nguyên để lấy tất cả địa chỉ của người dùng.
     */
    public function index(Request $request, string $nguoi_dung_id = null)
    {
        try {
            $query = DiaChi::query();

            $userId = $nguoi_dung_id ?? $request->input('nguoi_dung_id');

            if ($userId) {
                if (!User::where('nguoi_dung_id', $userId)->exists()) {
                    return response()->json(['message' => 'Người dùng không tồn tại.'], 404);
                }
                $query->where('nguoi_dung_id', $userId);
            }

            $diaChis = $query->get();

            return response()->json($diaChis, 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy danh sách địa chỉ: ' . $e->getMessage());
            return response()->json(['message' => 'Có lỗi xảy ra khi lấy danh sách địa chỉ.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Lưu một địa chỉ mới vào cơ sở dữ liệu.
     * Đã bỏ logic xử lý 'mac_dinh'.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'nguoi_dung_id' => 'required|exists:nguoi_dung,nguoi_dung_id',
                'dia_chi' => 'required|string|max:255',
                // 'mac_dinh' đã bị loại bỏ khỏi validation
            ], [
                'nguoi_dung_id.required' => 'Mã người dùng là bắt buộc.',
                'nguoi_dung_id.exists' => 'Người dùng không tồn tại.',
                'dia_chi.required' => 'Địa chỉ là bắt buộc.',
                'dia_chi.string' => 'Địa chỉ phải là chuỗi ký tự.',
                'dia_chi.max' => 'Địa chỉ không được vượt quá 255 ký tự.',
            ]);

            // DB::beginTransaction(); // Không cần transaction nếu không có logic liên quan đến nhiều bản ghi

            // Tạo địa chỉ mới
            $diaChi = DiaChi::create($validatedData);

            // DB::commit();

            return response()->json(['message' => 'Địa chỉ đã được thêm thành công.', 'data' => $diaChi], 201);
        } catch (ValidationException $e) {
            // DB::rollBack();
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            // DB::rollBack();
            Log::error('Có lỗi xảy ra khi tạo địa chỉ mới: ' . $e->getMessage());
            return response()->json(['message' => 'Có lỗi xảy ra khi tạo địa chỉ mới.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Hiển thị thông tin của một địa chỉ cụ thể.
     */
    public function show(string $id)
    {
        try {
            $diaChi = DiaChi::findOrFail($id);
            return response()->json($diaChi, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Không tìm thấy địa chỉ.'], 404);
        } catch (\Exception $e) {
            Log::error('Có lỗi xảy ra khi lấy thông tin địa chỉ: ' . $e->getMessage());
            return response()->json(['message' => 'Có lỗi xảy ra khi lấy thông tin địa chỉ.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Cập nhật thông tin một địa chỉ hiện có.
     * Đã bỏ logic xử lý 'mac_dinh'.
     */
    public function update(Request $request, string $id)
    {
        try {
            $diaChi = DiaChi::findOrFail($id);

            $validatedData = $request->validate([
                'nguoi_dung_id' => 'sometimes|required|exists:nguoi_dung,nguoi_dung_id',
                'dia_chi' => 'sometimes|required|string|max:255',
                // 'mac_dinh' đã bị loại bỏ khỏi validation
            ], [
                'nguoi_dung_id.required' => 'Mã người dùng là bắt buộc.',
                'nguoi_dung_id.exists' => 'Người dùng không tồn tại.',
                'dia_chi.required' => 'Địa chỉ là bắt buộc.',
                'dia_chi.string' => 'Địa chỉ phải là chuỗi ký tự.',
                'dia_chi.max' => 'Địa chỉ không được vượt quá 255 ký tự.',
            ]);

            // DB::beginTransaction(); // Không cần transaction nếu không có logic liên quan đến nhiều bản ghi

            // Logic xử lý 'mac_dinh' đã bị loại bỏ hoàn toàn
            // if (isset($validatedData['mac_dinh']) && $validatedData['mac_dinh'] === true) {
            //     DiaChi::where('nguoi_dung_id', $diaChi->nguoi_dung_id)
            //           ->where('id_dia_chi', '!=', $diaChi->id_dia_chi)
            //           ->update(['mac_dinh' => false]);
            // }

            $diaChi->update($validatedData);

            // DB::commit();

            return response()->json(['message' => 'Địa chỉ đã được cập nhật thành công.', 'data' => $diaChi], 200);
        } catch (ModelNotFoundException $e) {
            // DB::rollBack();
            return response()->json(['message' => 'Không tìm thấy địa chỉ để cập nhật.'], 404);
        } catch (ValidationException $e) {
            // DB::rollBack();
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            // DB::rollBack();
            Log::error('Có lỗi xảy ra khi cập nhật địa chỉ: ' . $e->getMessage());
            return response()->json(['message' => 'Có lỗi xảy ra khi cập nhật địa chỉ.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Xóa một địa chỉ khỏi cơ sở dữ liệu.
     */
    public function destroy(string $id)
    {
        try {
            $diaChi = DiaChi::findOrFail($id);
            $diaChi->delete();

            return response()->json(['message' => 'Địa chỉ đã được xóa thành công.'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Không tìm thấy địa chỉ để xóa.'], 404);
        } catch (\Exception $e) {
            Log::error('Có lỗi xảy ra khi xóa địa chỉ: ' . $e->getMessage());
            return response()->json(['message' => 'Có lỗi xảy ra khi xóa địa chỉ.', 'error' => $e->getMessage()], 500);
        }
    }

    // Phương thức diaChiMacDinh này có thể xóa hoàn toàn nếu không sử dụng
    public function diaChiMacDinh($nguoi_dung_id)
    {
        // Vì bạn không dùng mac_dinh, phương thức này sẽ chỉ trả về địa chỉ gần nhất (theo id_dia_chi)
        // hoặc bạn có thể xóa nó hoàn toàn nếu không cần một địa chỉ "đặc biệt" nào
        $diaChi = DiaChi::where('nguoi_dung_id', $nguoi_dung_id)
            ->orderByDesc('id_dia_chi') // Lấy địa chỉ cuối cùng được thêm vào
            ->with('nguoiDung')
            ->first();

        return response()->json($diaChi);
    }
}