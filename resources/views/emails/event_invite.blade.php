<!DOCTYPE html>
<html>
<head>
    <title>You're Invited!</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }
        .email-container {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 20px;
        }
        .content h2 {
            margin-top: 0;
            color: #4CAF50;
        }
        .content p {
            margin: 10px 0;
            line-height: 1.6;
        }
        .content strong {
            color: #333;
        }
        .footer {
            background-color: #f4f4f4;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
        .button {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .button:hover {
            background-color: #45a049;
        }
        .location {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        @media (max-width: 600px) {
            .email-container {
                width: 90%;
            }
            .header h1 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>You're Invited!</h1>
        </div>

        <div class="content">
            <h2>{{ $event->title }}</h2>
            <p>{{ $event->description }}</p>
            <p><strong>Date:</strong> {{ $event->date }}</p>
            <p><strong>Time:</strong> {{ $event->start_time }} to {{ $event->end_time }}</p>
            <div class="location">
                <p><strong>Location:</strong> {{ $event->location_type === 'physical' ? $event->location_address : 'Online' }}</p>
            </div>
            {{-- <a href="#" class="button">RSVP Now</a> --}}
        </div>
        <div class="footer">
            <p>Thank you for your interest in our event!</p>
            <p>&copy; {{ date('Y') }} Your Company. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
