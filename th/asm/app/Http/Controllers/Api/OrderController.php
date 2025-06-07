<?php

// app/Http/Controllers/Api/OrderController.php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('user')->get();
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
