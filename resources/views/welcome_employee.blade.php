<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; padding: 20px; }
        .card { background-color: #ffffff; padding: 30px; border-radius: 8px; border-top: 5px solid #FDE047; max-width: 500px; margin: 0 auto; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .text-accent { color: #EAB308; }
    </style>
</head>
<body>
    <div class="card">
        <h2 class="text-accent">Welcome, {{ $details['name'] }}!</h2>
        <p>Your official employee account for the <strong>CNHS-JHS HR System</strong> has been successfully created by the HR Department.</p>
        
        <p>Here are your secure login credentials:</p>
        <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>ID Number:</strong> {{ $details['id_number'] }}</p>
            <p style="margin: 5px 0;"><strong>Temporary Password:</strong> {{ $details['password'] }}</p>
        </div>

        <p>Please log into the system as soon as possible to update your Personal Data Sheet (PDS) and change your password.</p>
        
        <p>Thank you,<br><strong>HR Department</strong></p>
    </div>
</body>
</html>