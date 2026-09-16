<?php

namespace App\Http\Controllers;

use App\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Campay server-to-server webhook.
 * Dashboard sample callback: https://cwacam.org/campay/webhook
 */
class CampayWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $secret = trim((string) (
            config('services.campay.webhook_secret')
            ?: getenv('CAMPAY_WEBHOOK_SECRET')
            ?: ''
        ));

        if ($secret !== '') {
            $provided = (string) (
                $request->header('X-Campay-Webhook-Secret')
                ?: $request->header('X-Webhook-Secret')
                ?: $request->input('webhook_secret')
                ?: $request->input('secret')
                ?: ''
            );
            // Some Campay apps send the app webhook code as Authorization bearer/token.
            if ($provided === '' && $request->bearerToken()) {
                $provided = (string) $request->bearerToken();
            }
            if ($provided !== '' && ! hash_equals($secret, $provided)) {
                Log::warning('Campay webhook rejected: bad secret');

                return response()->json(['ok' => false], 401);
            }
        }

        $status = strtoupper((string) (
            $request->input('status')
            ?: $request->input('transaction_status')
            ?: ''
        ));
        $orderId = $request->input('external_reference')
            ?: $request->input('external_ref')
            ?: $request->input('externalReference');
        $reference = $request->input('reference')
            ?: $request->input('transaction_reference')
            ?: $request->input('operator_reference');

        if (! $orderId) {
            Log::info('Campay webhook missing external_reference', [
                'keys' => array_keys($request->all()),
            ]);

            return response()->json(['ok' => true, 'ignored' => true]);
        }

        $order = Order::where('id', $orderId)->where('is_donation', 1)->first();
        if (! $order) {
            return response()->json(['ok' => true, 'ignored' => true]);
        }

        $paymentStatus = 0;
        if (in_array($status, ['SUCCESSFUL', 'SUCCESS', 'COMPLETED'], true)) {
            $paymentStatus = 1;
        } elseif (in_array($status, ['FAILED', 'FAIL', 'CANCELLED', 'CANCELED'], true)) {
            $paymentStatus = 2;
        }

        if ($paymentStatus > 0) {
            $order->payment_status = $paymentStatus;
            if ($paymentStatus === 1) {
                $order->order_status = 1;
            }
            if ($reference) {
                if (Schema::hasColumn('orders', 'reference')) {
                    $order->reference = $reference;
                } else {
                    $order->token = $reference;
                }
            }
            $order->save();
        }

        return response()->json(['ok' => true]);
    }
}
