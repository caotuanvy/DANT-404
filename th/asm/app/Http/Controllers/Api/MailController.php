<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    public function sendEmail(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        try {
            // Gửi mail
            Mail::raw(
                "Bạn nhận được tin nhắn từ: {$validated['name']} ({$validated['email']})\n\n"
                ."Nội dung:\n".$validated['message'],
                function ($msg) use ($validated) {
                    $msg->to('404notfuond@gmail.com') // mail nhận
                        ->subject($validated['subject']);
                }
            );

            return response()->json([
                'message' => 'Gửi email thành công!'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Không thể gửi email. Lỗi: '.$e->getMessage()
            ], 500);
        }
    }
}
