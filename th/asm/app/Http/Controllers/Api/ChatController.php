<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    /**
     * Gửi tin nhắn mới.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendMessage(Request $request)
    {
        try {
            $request->validate([
                'conversation_id' => 'required|integer|exists:conversations,id',
                'sender_id' => 'required|integer|exists:nguoi_dung,nguoi_dung_id',
                'content' => 'required|string',
            ]);

            $message = Message::create([
                'conversation_id' => $request->input('conversation_id'),
                'sender_id' => $request->input('sender_id'),
                'content' => $request->input('content'),
            ]);

            return response()->json(['message' => $message], 201);
        } catch (\Exception $e) {
            Log::error('Lỗi khi gửi tin nhắn: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Có lỗi xảy ra khi gửi tin nhắn.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Lấy tất cả tin nhắn từ một cuộc trò chuyện.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMessages(Request $request)
    {
        try {
            $request->validate([
                'conversation_id' => 'required|integer|exists:conversations,id',
            ]);

            $messages = Message::where('conversation_id', $request->input('conversation_id'))
                               ->orderBy('created_at', 'asc')
                               ->get();

            return response()->json($messages, 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy tin nhắn: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Có lỗi xảy ra khi lấy tin nhắn.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Lấy hoặc tạo một conversation giữa user và admin.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrCreateConversation(Request $request)
    {
        try {
            $request->validate([
                'nguoi_dung_id' => 'required|integer|exists:nguoi_dung,nguoi_dung_id',
                'admin_id' => 'nullable|integer|exists:nguoi_dung,nguoi_dung_id',
            ]);

            $nguoiDungId = $request->input('nguoi_dung_id');
            $adminId = $request->input('admin_id');

            // Tìm conversation tồn tại
            $conversation = Conversation::where('nguoi_dung_id', $nguoiDungId)
                                         ->where('admin_id', $adminId)
                                         ->first();

            // Nếu không có, tạo conversation mới
            if (!$conversation) {
                $conversation = Conversation::create([
                    'nguoi_dung_id' => $nguoiDungId,
                    'admin_id' => $adminId,
                ]);
            }

            return response()->json(['conversation_id' => $conversation->id], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy hoặc tạo conversation: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Có lỗi xảy ra khi xử lý conversation.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Lấy danh sách tất cả conversations cho giao diện admin.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConversationsForAdmin()
    {
        try {
            $conversations = Conversation::with(['user', 'admin', 'messages' => function ($query) {
                $query->latest()->take(1);
            }])
            ->whereHas('messages') // Chỉ lấy những conversation đã có tin nhắn
            ->withLastMessageDate()
            ->orderByDesc('last_message_date')
            ->get();

            return response()->json($conversations, 200);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy danh sách conversations cho admin: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Có lỗi xảy ra khi lấy danh sách conversations.', 'error' => $e->getMessage()], 500);
        }
    }
}