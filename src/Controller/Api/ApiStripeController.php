<?php

namespace App\Controller\Api;

use Stripe\StripeClient;
use App\Services\StripeService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiStripeController extends AbstractController
{
    #[Route('/api/stripe/payment-intent', name: 'app_stripe_payment-intent', methods: ['POST'])]
    public function index(
        StripeService $stripeService,
        Request $req,
    ): Response {

        try {

            $stripeSecretKey = $stripeService->getPrivateKey();
            $items = $req->getPayload()->get("items");
            // dd($items);
            $stripe = new StripeClient($stripeSecretKey);

            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => $this->calculateOrderAmount($items),
                'currency' => 'eur',
                // In the latest version of the API, specifying the `automatic_payment_methods` parameter is optional because Stripe enables its functionality by default.
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            $output = [
                'clientSecret' => $paymentIntent->client_secret,
            ];

            return $this->json($output);
        } catch (\Throwable $th) {
            return $this->json(['error' => $th->getMessage()]);
        }
    }

    public function calculateOrderAmount($cart)
    {
        return 2500;
        // return $cart->sub_total_with_carrier;
    }
}
