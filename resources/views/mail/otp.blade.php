<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>{{ $purpose === 'verify' ? 'Xác thực email' : 'Đặt lại mật khẩu' }}</title>
</head>
<body>
    <p>
        @if ($purpose === 'verify')
            Mã xác thực email HarvestHub của bạn là:
        @else
            Mã đặt lại mật khẩu HarvestHub của bạn là:
        @endif
    </p>
    <p style="font-size: 24px; font-weight: bold; letter-spacing: 4px;">{{ $code }}</p>
    <p>Mã có hiệu lực trong 10 phút. Nếu bạn không yêu cầu mã này, hãy bỏ qua email.</p>
</body>
</html>
