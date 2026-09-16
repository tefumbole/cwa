<?php

namespace App\Http\Controllers;

use App\Order;
use App\Services\CampayService;
use App\Support\CameroonMomoNetwork;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DonateController extends Controller
{
    public function show()
    {
        return view('beyond.donate', [
            'presets' => [2000, 5000, 10000, 25000, 50000],
        ]);
    }

    public function holder(Request $request)
    {
        $campay = app(CampayService::class);
        $phone = $campay->normalizePhone($request->input('phone'));
        $local = CameroonMomoNetwork::localDigits($phone);
        if (strlen($local) < 9) {
            return response()->json(['ok' => false, 'name' => null, 'operator' => null]);
        }

        $operator = CameroonMomoNetwork::detect($phone);
        $name = null;
        try {
            $hit = app(MobileMoneyHolderService::class)->lookup($phone);
            if (! empty($hit['name'])) {
                $name = $hit['name'];
            }
            if (! empty($hit['operator'])) {
                $operator = $hit['operator'];
            }
        } catch (\Throwable $e) {
            // Prefix detection still informs MTN vs Orange.
        }

        return response()->json([
            'ok' => true,
            'name' => $name,
            'operator' => $operator,
            'operator_label' => CameroonMomoNetwork::label($operator),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
            'amount' => 'required|numeric|min:100',
        ]);

        $campay = app(CampayService::class);
        $phone = $campay->normalizePhone($request->input('phone'));
        if (strlen($phone) < 12) {
            return back()->withInput()->with('not_permitted', __('cwa.donate.invalid_phone'));
        }

        $operator = CameroonMomoNetwork::fromApiValue($request->input('operator'))
            ?: CameroonMomoNetwork::detect($phone);
        $donorName = trim((string) $request->input('donor_name'));
        if ($donorName === '' || preg_match('/^\+?\d{8,}$/', $donorName)) {
            $donorName = 'Donor';
        }

        $amount = (int) $request->input('amount');
        $order = Order::create([
            'name' => $donorName,
            'phone' => $phone,
            'email' => null,
            'address' => 'CWACAM donation',
            'city' => 'Cameroon',
            'state' => '',
            'description' => 'Donate',
            'order_status' => 2,
            'payment_status' => 0,
            'payment_method' => $operator === 'orange' ? 'OM' : 'MOMO',
            'grand_total' => $amount,
            'is_donation' => 1,
        ]);

        $callback = route('beyond.donate.callback');
        $link = $campay->createPaymentLink(
            $amount,
            $phone,
            $callback,
            $order->id,
            'CWACAM donation',
            CameroonMomoNetwork::campayOption($operator)
        );

        if (! $link) {
            $order->payment_status = 2;
            $order->save();

            return back()->withInput()->with('not_permitted', __('cwa.donate.failed'));
        }

        return redirect()->away($link);
    }

    public function callback(Request $request)
    {
        $orderId = $request->input('external_reference');
        $status = strtoupper((string) $request->input('status'));
        $reference = $request->input('reference');

        $order = Order::where('id', $orderId)->where('is_donation', 1)->first();
        if (! $order) {
            return redirect()->route('beyond.donate')->with('not_permitted', __('cwa.donate.not_found'));
        }

        $paymentStatus = 0;
        if ($status === 'SUCCESSFUL') {
            $paymentStatus = 1;
        } elseif ($status === 'FAILED') {
            $paymentStatus = 2;
        }

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

        if ($paymentStatus === 1) {
            return redirect()->route('beyond.donate.thanks')->with('donation_amount', $order->grand_total);
        }
        if ($paymentStatus === 2) {
            return redirect()->route('beyond.donate')->with('not_permitted', 'Payment was not completed. You can try again.');
        }

        return redirect()->route('beyond.donate')->with('not_permitted', 'Payment is still pending. Confirm on your phone and refresh shortly.');
    }

    public function thanks()
    {
        return view('beyond.donate-thanks');
    }
}
