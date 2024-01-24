<?php

namespace App\Controller\Api;

use App\Repository\OrderRepository;
use Stripe\StripeClient;
use App\Services\StripeService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiStripeController extends AbstractController
{
    #[Route('/api/stripe/payment-intent/{orderId}', name: 'app_stripe_payment-intent', methods: ['POST'])]
    public function index(
        $orderId,
        StripeService $stripeService,
        Request $req,
        OrderRepository $orderRepo,
    ): Response {

        try {

            $stripeSecretKey = $stripeService->getPrivateKey();
            $order = $orderRepo->findOneById($orderId);
            if (!$order) {
                return $this->json(['error' => "Order not found"]);
            }

            $stripe = new StripeClient($stripeSecretKey);

            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => $order->getOrderCostTtc(),
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
