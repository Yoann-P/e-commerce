<?php

namespace App\Controller;

use App\Services\CartService;
use App\Repository\AddressRepository;
use App\Services\StripeService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CheckoutController extends AbstractController
{
    public function __construct(
        private CartService $cartService,
        private RequestStack $requestStack,
    ) {
        $this->cartService = $cartService;
        $this->session = $requestStack->getsession();
    }

    #[Route('/checkout', name: 'app_checkout')]
    public function index(
        AddressRepository $addressRepository,
        StripeService $stripeService,
    ): Response {

        $user = $this->getUser();
        $cart = $this->cartService->getCartDetails();

        if (!count($cart['items'])) {
            return $this->redirectToRoute('app_home');
        }

        if (!$user) {
            $this->session->set("next", "app_checkout");
            return $this->redirectToRoute('app_login');
        }

        $addresses = $addressRepository->findByUser($user);

        $cart_json = json_encode($cart);

        $publicKey = $stripeService->getPublicKey();

        return $this->render('checkout/index.html.twig', [
            'controller_name' => 'CheckoutController',
            'cart' => $cart,
            'cart_json' => $cart_json,
            'public_key' => $publicKey,
            'addresses' => $addresses,
        ]);
    }
}
