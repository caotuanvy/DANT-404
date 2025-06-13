<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class KichHoatTaiKhoan extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $link;

    public function __construct($user, $link)
    {
        $this->user = $user;
        $this->link = $link;
    }

    public function build()
    {
        return $this->subject('Kích hoạt tài khoản')
                    ->view('emails.kichhoat')
                    ->with([
                        'hoTen' => $this->user->ho_ten,
                        'activationLink' => $this->link,
                    ]);
    }
}
