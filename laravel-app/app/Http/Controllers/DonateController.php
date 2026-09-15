<?php

namespace App\Http\Controllers;

use App\Order;
use App\Services\CampayService;
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

    public function store(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
            'amount' => 'required|numeric|min:100',
        ]);

        $campay = app(CampayService::class);
        $phone = $campay->normalizePhone($request->input('phone'));
        if (strlen($phone) < 12) {
            return back()->withInput()->with('not_permitted', 'Enter a valid MoMo number (9 digits).');
        }

        $amount = (int) $request->input('amount');
        $order = Order::create([
            'name' => 'Donor',
            'phone' => $phone,
            'email' => null,
            'address' => 'CWACAM donation',
            'city' => 'Cameroon',
            'state' => '',
            'description' => 'Donate',
            'order_status' => 2,
            'payment_status' => 0,
            'payment_method' => 'MOMO',
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
            'MOMO'
        );

        if (! $link) {
            $order->payment_status = 2;
            $order->save();

            return back()->withInput()->with('not_permitted', 'MoMo payment could not start. Try again.');
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
            return redirect()->route('beyond.donate')->with('not_permitted', 'We could not find that donation.');
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
