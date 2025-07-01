<!DOCTYPE html>
<html>
<head>
    <title>Mã xác nhận khôi phục mật khẩu</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { width: 80%; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .header { background-color: #f8f8f8; padding: 10px; text-align: center; border-bottom: 1px solid #eee; }
        .code { font-size: 2em; font-weight: bold; color: #007bff; text-align: center; margin: 20px 0; padding: 15px; background-color: #e9f5ff; border-radius: 5px; letter-spacing: 5px; }
        .footer { font-size: 0.9em; color: #777; text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Mã Xác Nhận Khôi Phục Mật Khẩu</h2>
        </div>
        <p>Xin chào,</p>
        <p>Bạn đã yêu cầu đặt lại mật khẩu cho tài khoản của mình. Vui lòng sử dụng mã xác nhận sau để tiếp tục:</p>
        <div class="code">{{ $code }}</div>
        <p>Mã này sẽ hết hạn trong vòng **5 phút**. Vui lòng không chia sẻ mã này cho bất kỳ ai.</p>
        <p>Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này hoặc liên hệ với chúng tôi.</p>
        <div class="footer">
            <p>Trân trọng,</p>
            <p>Đội ngũ hỗ trợ của bạn</p>
        </div>
    </div>
</body>
</html>