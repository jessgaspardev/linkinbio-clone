<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    public function upgrade(Request $request)
    {
        if ($request->user()->subscribed('default')) {
            return $this->portal($request);
        }
    
        $checkout = $request->user()
            ->newSubscription('default', config('services.stripe.price_pro'))
            ->checkout([
                'success_url' => route('dashboard'),
                'cancel_url' => route('pages.index'),
            ]);
    
        $url = $checkout->toResponse($request)->headers->get('Location');
    
        return Inertia::location($url);
    }
    
    public function portal(Request $request)
    {
        $response = $request->user()->redirectToBillingPortal(route('pages.index'));
    
        return Inertia::location($response->headers->get('Location'));
    }
}
