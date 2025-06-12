<?php

namespace App\Http\Controllers\Api;

use App\Models\DiaChi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Controller;

class DiaChiController extends Controller
{
    /**
     * Lấy danh sách các địa chỉ.
     * Có thể lọc theo nguoi_dung_id từ query param hoặc từ URL segment.
     */
    public function index(Request $request, string $nguoi_dung_id = null) // Thêm $nguoi_dung_id vào đây
    {
        try {
            $query = DiaChi::query();

            // Ưu tiên nguoi_dung_id từ URL segment nếu có
            $userId = $nguoi_dung_id ?? $request->input('nguoi_dung_id');

            if ($userId) {
                // Kiểm tra xem người dùng có tồn tại không
                if (!User::where('nguoi_dung_id', $userId)->exists()) {
                    return response()->json(['message' => 'Người dùng không tồn tại.'], 404);
                }
                $query->where('nguoi_dung_id', $userId);
            }

            $diaChis = $query->get();

            return response()->json($diaChis, 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Có lỗi xảy ra khi lấy danh sách địa chỉ.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Lưu một địa chỉ mới vào cơ sở dữ liệu.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'nguoi_dung_id' => 'required|exists:nguoi_dung,nguoi_dung_id',
                'dia_chi' => 'required|string|max:255',
            ], [
                'nguoi_dung_id.required' => 'Mã người dùng là bắt buộc.',
                'nguoi_dung_id.exists' => 'Người dùng không tồn tại.',
                'dia_chi.required' => 'Địa chỉ là bắt buộc.',
                'dia_chi.string' => 'Địa chỉ phải là chuỗi ký tự.',
                'dia_chi.max' => 'Địa chỉ không được vượt quá 255 ký tự.',
            ]);

            $diaChi = DiaChi::create($validatedData);

            return response()->json($diaChi, 201);

        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
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
            return response()->json(['message' => 'Có lỗi xảy ra khi lấy thông tin địa chỉ.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Cập nhật thông tin một địa chỉ hiện có.
     */
    public function update(Request $request, string $id)
    {
        try {
            $diaChi = DiaChi::findOrFail($id);

            $validatedData = $request->validate([
                'nguoi_dung_id' => 'sometimes|required|exists:nguoi_dung,nguoi_dung_id',
                'dia_chi' => 'sometimes|required|string|max:255',
            ], [
                'nguoi_dung_id.required' => 'Mã người dùng là bắt buộc.',
                'nguoi_dung_id.exists' => 'Người dùng không tồn tại.',
                'dia_chi.required' => 'Địa chỉ là bắt buộc.',
                'dia_chi.string' => 'Địa chỉ phải là chuỗi ký tự.',
                'dia_chi.max' => 'Địa chỉ không được vượt quá 255 ký tự.',
            ]);

            $diaChi->update($validatedData);

            return response()->json($diaChi, 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Không tìm thấy địa chỉ để cập nhật.'], 404);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
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
            return response()->json(['message' => 'Có lỗi xảy ra khi xóa địa chỉ.', 'error' => $e->getMessage()], 500);
        }
    }
}