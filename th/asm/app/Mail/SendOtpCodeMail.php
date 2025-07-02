<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendOtpCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $code; // Biến này sẽ được truyền vào view email

    public function __construct($code)
    {
        $this->code = $code;
    }

    public function build()
    {
        return $this->subject('Mã xác nhận khôi phục mật khẩu của bạn')
                    ->view('emails.otp_code'); // Sử dụng view emails/otp_code.blade.php
    }
}