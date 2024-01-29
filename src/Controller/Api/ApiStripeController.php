<?php

namespace App\Controller\Api;

use Stripe\StripeClient;
use App\Services\StripeService;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
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
        EntityManagerInterface $em,
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

            $order->setStripeClientSecret($paymentIntent->client_secret);
            $em->persist($order);
            $em->flush();

            return $this->json($output);
        } catch (\Throwable $th) {
            return $this->json(['error' => $th->getMessage()]);
        }
    }
}
