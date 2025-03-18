<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stripe Onboarding Success</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .card {
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .btn-primary {
            border-radius: 50px;
        }
    </style>
</head>
<body>

    <div class="card text-center">
        <h2 class="mb-4 text-success">🎉 Onboarding Completed!</h2>
        <p class="mb-4">Your Stripe account onboarding is successful. You can now manage your payouts and settings from your dashboard.</p>
        <a href="{{ url('/') }}" class="btn btn-primary">Go to Dashboard</a>
    </div>

</body>
</html>
