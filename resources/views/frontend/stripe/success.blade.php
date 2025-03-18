<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stripe Onboarding Success</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #e0f7fa, #ffffff);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1rem;
        }

        .success-card {
            background: #fff;
            padding: 2rem;
            border-radius: 1.5rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .success-icon {
            font-size: 4rem;
            color: #28a745;
        }

        .btn-primary {
            border-radius: 50px;
            padding: 0.75rem 1.5rem;
        }

        @media (max-width: 480px) {
            .success-card {
                padding: 1.5rem;
            }
            .success-icon {
                font-size: 3rem;
            }
            h2 {
                font-size: 1.5rem;
            }
            p {
                font-size: 0.9rem;
            }
        }
    </style>
</head>

<body>

    <div class="success-card">
        <div class="success-icon mb-3">🎉</div>
        <h2 class="mb-3 text-success">Onboarding Successful!</h2>
        <p class="mb-4">You have successfully completed your Stripe onboarding. You can now manage your business from your dashboard.</p>
        <a href="{{ url('/') }}" class="btn btn-primary w-100">Go to Back</a>
    </div>

</body>

</html>
