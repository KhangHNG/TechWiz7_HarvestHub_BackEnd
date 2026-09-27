<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Verify email</title>
</head>
<body>
    <p>Hello,</p>
    <p>Click the button below to verify your HarvestHub email. The link is valid for 60 minutes.</p>
    <p>
        <a href="{{ $url }}" style="display: inline-block; padding: 12px 20px; background: #2f6b3a; color: #ffffff; text-decoration: none; border-radius: 6px;">Verify email</a>
    </p>
    <p>If the button does not work, copy this link into your browser:</p>
    <p><a href="{{ $url }}">{{ $url }}</a></p>
    <p>If you did not create a HarvestHub account, please ignore this email.</p>
</body>
</html>
