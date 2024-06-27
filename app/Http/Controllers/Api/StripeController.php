<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Models\{ Product, User };

class StripeController extends Controller
{
    public function checkout(Request $request) {
        $request->validate([
            'product' => 'required|integer'
        ]);

        $product = Product::findOrFail($request->product);

        \Stripe\Stripe::setApiKey(getenv("STRIPE_SECRET"));

        $stripeCheckoutSession = \Stripe\Checkout\Session::create([
            'line_items' => [[
                'price' => $product->stripe_price_id,
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'allow_promotion_codes' => true,
            'metadata' => [
                'user_id' => $request->user()->id
            ],
          'success_url' => 'http://127.0.0.1:5173/checkout/success', // enter your app success url to redirect user
          'cancel_url' => 'http://127.0.0.1:5173/checkout/cancel', // enter your app cancel url to redirect user
        ]);

        return response()->json(['url' => $stripeCheckoutSession->url]);
    }

    public function webhook() {
        $stripe = new \Stripe\StripeClient(getenv("STRIPE_SECRET"));

        $endpoint_secret = getenv("STRIPE_WEBHOOK_SECRET");

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try {
          $event = \Stripe\Webhook::constructEvent(
            $payload, $sig_header, $endpoint_secret
          );
        } catch(\UnexpectedValueException $e) {
          // Invalid payload
          http_response_code(400);
          exit();
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
          // Invalid signature
          http_response_code(400);
          exit();
        }

        if ($event->type == "checkout.session.completed") {
            $userId = $event->data->object->metadata->user_id;
            $stripeCustomerId = $event->data->object->customer;

            User::where('id', $userId)->update(['stripe_customer_id' => $stripeCustomerId]);
        }

        Log::debug($event);

        http_response_code(200);
    }

    public function customer(Request $request) {
        $stripe = new \Stripe\StripeClient(getenv("STRIPE_SECRET"));

        $stripeCustomerId = $request->user()->stripe_customer_id;

        if (!$stripeCustomerId) {
            return response()->json(['error' => "User does not have subscription"], 400);
        }

        $customerPortal = $stripe->billingPortal->sessions->create([
          'customer' => $stripeCustomerId,
          'return_url' => 'http://127.0.0.1:5173/user', // enter your app user account url to redirect user
        ]);

        return response()->json(['url' => $customerPortal->url]);
    }
}
