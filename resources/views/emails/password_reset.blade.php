<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background-color: #ffffff; padding: 20px; }
        .card { background-color: #ffffff; padding: 30px; border-radius: 8px; border: 2px solid #1A3E6F; max-width: 500px; margin: 0 auto; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .text-header { color: #1A3E6F; text-align: center; font-weight: bold; }
        .btn { display: inline-block; background-color: #FACC15; color: #1A3E6F; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; text-align: center; }
        .footer { font-size: 12px; color: #64748b; margin-top: 30px; text-align: center; }
    </style>
</head>
<body>
    <div class="card">
        <h2 class="text-header">Password Reset Request</h2>
        <p>Hello, {{ $name }}!</p>
        <p>We received a request to reset your password for your <strong>CNHS-JHS HR System</strong> account.</p>
        
        <p>If you made this request, please click the button below to set a new password:</p>
        
        <div style="text-align: center;">
            <a href="{{ $resetUrl }}" class="btn">Reset Password</a>
        </div>

        <p class="footer">If you did not request a password reset, no further action is required.</p>
        
        <p style="margin-top: 20px;">Thank you,<br><strong>HR Department</strong></p>
    </div>
</body>
</html>
