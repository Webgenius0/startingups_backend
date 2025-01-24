{{-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stripe Payment</title>
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;
        }
        .container {
            max-width: 500px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
        }
        #card-element {
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        #submit {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #6772e5;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        #submit:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        .message {
            margin-top: 20px;
            padding: 10px;
            border-radius: 5px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Stripe Payment</h1>
        <form id="payment-form">
            <div id="card-element">
                <!-- Stripe Elements will be inserted here -->
            </div>
            <button id="submit" type="submit">Pay</button>
        </form>
        <div id="payment-result" class="message" style="display: none;"></div>
    </div>

    <script>
        const stripe = Stripe("{{ config('services.stripe.key') }}");
        const elements = stripe.elements();
        const cardElement = elements.create("card");
        cardElement.mount("#card-element");

        const form = document.getElementById("payment-form");
        const submitButton = document.getElementById("submit");
        const paymentResult = document.getElementById("payment-result");

        form.addEventListener("submit", async (event) => {
            event.preventDefault();
            submitButton.disabled = true;

            try {
                // Step 1: Create a payment intent via backend
                const response = await fetch("{{ url('/stripe/create-payment-intent') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    },
                    body: JSON.stringify({
                        event_booking_id: 1, // Replace with the actual event booking ID
                        amount: 50.0, // Replace with the actual amount
                    }),
                });

                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.message || "Failed to create payment intent.");
                }

                const clientSecret = data.data.client_secret;

                // Step 2: Confirm payment on the frontend
                const { error, paymentIntent } = await stripe.confirmCardPayment(clientSecret, {
                    payment_method: {
                        card: cardElement,
                    },
                });

                if (error) {
                    showMessage("Payment failed: " + error.message, "error");
                } else if (paymentIntent.status === "succeeded") {
                    showMessage("Payment succeeded!", "success");
                }
            } catch (error) {
                showMessage(error.message, "error");
            } finally {
                submitButton.disabled = false;
            }
        });

        function showMessage(message, type) {
            paymentResult.textContent = message;
            paymentResult.className = "message " + type;
            paymentResult.style.display = "block";
        }
    </script>
</body>
</html> --}}
