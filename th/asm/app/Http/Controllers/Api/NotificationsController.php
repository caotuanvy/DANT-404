<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notifications;

class NotificationsController extends Controller
{
    public function index()
    {
        // Lấy danh sách thông báo mới nhất
        return Notifications::orderBy('ngay_tao', 'desc')->limit(50)->get();
    }

    public function markAsRead($id)
    {
        $noti = Notifications::find($id);
        if ($noti) {
            $noti->da_xem = 1;
            $noti->save();
        }
        return response()->json(['success' => true]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'loai_thong_bao' => 'required|string',
            'mo_ta' => 'nullable|string',
            'tin_bao' => 'required|string',
        ]);
        $noti = Notifications::create([
            'loai_thong_bao' => $request->loai_thong_bao,
            'mo_ta' => $request->mo_ta,
            'tin_bao' => $request->tin_bao,
            'da_xem' => 0,
            'ngay_tao' => now(),
        ]);
        return response()->json($noti, 201);
    }
    public function markAllAsRead()
{
    // Đánh dấu tất cả thông báo là đã xem
    Notifications::where('da_xem', 0)->update(['da_xem' => 1]);
    return response()->json(['success' => true]);
}
}
