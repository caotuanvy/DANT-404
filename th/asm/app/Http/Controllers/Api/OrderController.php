<?php

// app/Http/Controllers/Api/OrderController.php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderController extends Controller
{
   public function index(Request $request)
{
    $query = Order::whereNull('ngay_xoa')
        ->with('user', 'paymentMethod', 'orderItems.bien_the.sanPham', 'diachi');

    if ($request->has('search') && $request->search) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('id', 'like', "%$search%")
              ->orWhereHas('user', function($q2) use ($search) {
                  $q2->where('ho_ten', 'like', "%$search%");
              });
        });
    }

    $orders = $query->get();
    return response()->json($orders);
}
    public function hideOrder($id)
    {
        $order = Order::findOrFail($id);
        $order->ngay_xoa = now(); // hoặc Carbon::now()
        $order->save();
        return response()->json(['message' => 'Đã ẩn đơn hàng']);
    }
    // public function approve($id)
    // {
    //     $order = Order::findOrFail($id);
    //     $order->status = 'approved';
    //     $order->save();

    //     return response()->json(['message' => 'Đơn hàng đã được duyệt']);
    // }

    // public function reject($id)
    // {
    //     $order = Order::findOrFail($id);
    //     $order->status = 'rejected';
    //     $order->save();

    //     return response()->json(['message' => 'Đơn hàng đã bị từ chối']);
    // }
    public function updateStatus(Request $request, $id)
        {
            $order = Order::findOrFail($id);
            $order->trang_thai = $request->input('trang_thai');
            $order->save();
            return response()->json($order);
        }
}
