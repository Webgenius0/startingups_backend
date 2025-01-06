<?php

namespace App\Http\Controllers\Api\Business\Backend;


use Stripe\Webhook;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class StripeWebhookController extends Controller
{

    use ApiResponse;





    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret'); // From .env

        try {
            // Verify the Stripe signature
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);

            // Handle different types of events
            switch ($event->type) {
                case 'invoice.payment_succeeded':
                    $this->paymentSucceeded($event->data->object);
                    break;
                case 'customer.subscription.updated':
                    $this->subscriptionUpdated($event->data->object);
                    break;
                case 'customer.subscription.deleted':
                    $this->subscriptionDeleted($event->data->object);
                    break;
                default:
                    Log::info('Unhandled event type: ' . $event->type);
            }

            return $this->success([], 'Webhook processed successfully.', 200);
        } catch (\Exception $e) {
            Log::error('Webhook Error: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid webhook'], 400);
        }
    }

    protected function paymentSucceeded($invoice)
    {
        // Example: Update user's subscription or notify the user
        Log::info('Payment succeeded for invoice: ' . $invoice->id);

        // Add your logic here
    }

    protected function subscriptionUpdated($subscription)
    {
        // Example: Update subscription in database
        Log::info('Subscription updated: ' . $subscription->id);

        // Add your logic here
    }

    protected function subscriptionDeleted($subscription)
    {
        // Example: Handle subscription cancellation
        Log::info('Subscription deleted: ' . $subscription->id);

        // Add your logic here
    }
}
