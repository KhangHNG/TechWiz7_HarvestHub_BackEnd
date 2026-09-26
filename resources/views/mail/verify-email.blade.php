<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Xác thực email</title>
</head>
<body>
    <p>Chào bạn,</p>
    <p>Nhấn nút bên dưới để xác thực email HarvestHub. Liên kết có hiệu lực trong 60 phút.</p>
    <p>
        <a href="{{ $url }}" style="display: inline-block; padding: 12px 20px; background: #2f6b3a; color: #ffffff; text-decoration: none; border-radius: 6px;">Xác thực email</a>
    </p>
    <p>Nếu nút không mở được, sao chép liên kết này vào trình duyệt:</p>
    <p><a href="{{ $url }}">{{ $url }}</a></p>
    <p>Nếu bạn không tạo tài khoản HarvestHub, hãy bỏ qua email này.</p>
</body>
</html>
