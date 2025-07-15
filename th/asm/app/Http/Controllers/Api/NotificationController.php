<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    protected $messaging;

    public function __construct(Messaging $messaging)
    {
        $this->messaging = $messaging;
    }

    public function saveFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $user = $request->user();

        if ($user) {
            $user->fcm_token = $request->fcm_token;
            $user->save();
            return response()->json(['message' => 'FCM token đã được lưu thành công.']);
        }

        return response()->json(['message' => 'Người dùng chưa được xác thực hoặc không tìm thấy.'], 401);
    }

    public function getUsersForNotification()
    {
        $users = User::select('nguoi_dung_id', 'ho_ten', 'email', 'fcm_token')
            ->whereNotNull('fcm_token')
            ->get();

        return response()->json($users);
    }

    public function sendNotification(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'body' => 'required|string',
            'user_id' => 'sometimes|exists:nguoi_dung,nguoi_dung_id',
            'topic' => 'sometimes|string',
        ]);

        $title = $request->input('title');
        $body = $request->input('body');
        $userId = $request->input('user_id');
        $topic = $request->input('topic');

        try {
            if ($userId) {
                $user = User::find($userId);

                if ($user && $user->fcm_token) {
                    $message = CloudMessage::fromArray([
                        'token' => $user->fcm_token,
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                        ],
                    ]);

                    $this->messaging->send($message);

                    return response()->json(['message' => 'Thông báo đã được gửi đến người dùng thành công!']);
                } else {
                    return response()->json(['message' => 'Không tìm thấy người dùng hoặc người dùng chưa có FCM token.'], 404);
                }
            } elseif ($topic) {
                $message = CloudMessage::fromArray([
                    'topic' => $topic,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                ]);

                $this->messaging->send($message);

                return response()->json(['message' => 'Thông báo đã được gửi đến chủ đề thành công!']);
            } else {
                return response()->json(['message' => 'Vui lòng chọn người dùng hoặc nhập chủ đề để gửi thông báo.'], 400);
            }
        } catch (\Exception $e) {
            Log::error('Lỗi gửi thông báo Firebase: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Gửi thông báo thất bại: ' . $e->getMessage()], 500);
        }
    }
}
