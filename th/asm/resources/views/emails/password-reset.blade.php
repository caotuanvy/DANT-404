@component('mail::message')
# Mật khẩu mới của bạn

Chào bạn,

Bạn đã yêu cầu đặt lại mật khẩu cho tài khoản của mình trên {{ config('app.name') }}.

Mật khẩu mới của bạn là: **{{ $newPassword }}**

Chúng tôi khuyên bạn nên đăng nhập và thay đổi mật khẩu này thành mật khẩu dễ nhớ hơn ngay sau khi đăng nhập thành công.

Nếu bạn không yêu cầu việc này, vui lòng bỏ qua email này hoặc liên hệ với bộ phận hỗ trợ của chúng tôi.

Trân trọng,
Đội ngũ {{ config('app.name') }}
@endcomponent