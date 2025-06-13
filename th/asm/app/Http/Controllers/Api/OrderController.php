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
        $query = Order::with('user', 'paymentMethod', 'orderItems', 'orderItems.bienThe.sanPham'); // Thêm 'orderItems'

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('id', 'like', "%$search%")
                ->orWhereHas('user', function($q) use ($search) {
                    $q->where('ho_ten', 'like', "%$search%");
                });
        }

        $orders = $query->get();
        return response()->json($orders);
    }
    public function approve($id)
    {
        $order = Order::findOrFail($id);
        $order->status = 'approved';
        $order->save();

        return response()->json(['message' => 'Đơn hàng đã được duyệt']);
    }

    public function reject($id)
    {
        $order = Order::findOrFail($id);
        $order->status = 'rejected';
        $order->save();

        return response()->json(['message' => 'Đơn hàng đã bị từ chối']);
    }
}
