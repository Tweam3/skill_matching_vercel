<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reset Your Password</title>
</head>
<body style="font-family:Arial,sans-serif;background:#f5f5f5;padding:20px;">
    <div style="max-width:600px;margin:0 auto;background:#fff;padding:30px;border-radius:8px;">
        <h2 style="color:#06264A;">Reset Your Password</h2>
        <p style="color:#555;">Hi {{ $user->Full_Name }},</p>
        <p style="color:#555;">You requested to reset your password for the Skill Matching System. Click the button below to set a new password:</p>
        <p style="margin:20px 0;">
            <a href="{{ url('/reset-password/' . $token . '/' . $user->User_ID) }}" style="display:inline-block;padding:12px 24px;background:#06264A;color:#fff;text-decoration:none;border-radius:6px;font-weight:600;">Reset My Password</a>
        </p>
        <p style="color:#888;font-size:0.85rem;">This link will expire in 60 minutes.</p>
        <p style="color:#888;font-size:0.85rem;">If you did not request a password reset, you can safely ignore this email. Your password will remain unchanged.</p>
        <hr style="border:none;border-top:1px solid #eee;margin:20px 0;">
        <p style="color:#888;font-size:0.85rem;">Skill Matching System — ISAT-U</p>
    </div>
</body>
</html>