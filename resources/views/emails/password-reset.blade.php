<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset OTP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            color: #333333;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #e6e6e6;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #4CAF50;
        }
        .content {
            padding: 20px;
            text-align: center;
        }
        .content h2 {
            font-size: 20px;
            color: #555555;
        }
        .content p {
            font-size: 16px;
            margin-top: 10px;
            color: #777777;
        }
        .otp {
            display: inline-block;
            margin: 20px 0;
            font-size: 24px;
            font-weight: bold;
            color: #4CAF50;
            background-color: #f2f2f2;
            padding: 10px 20px;
            border-radius: 5px;
        }
        .footer {
            margin-top: 20px;
            font-size: 14px;
            color: #aaaaaa;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>Password Reset</h1>
        </div>
        <div class="content">
            <h2>Your OTP for password reset is:</h2>
            <div class="otp">{{ $otp }}</div>
            <p>This OTP will expire in <strong>10 minutes</strong>. Please use it to reset your password promptly.</p>
        </div>
        <div class="footer">
            <p>If you didn’t request a password reset, please ignore this email.</p>
        </div>
    </div>
</body>
</html>
