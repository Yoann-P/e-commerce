<?php

namespace App\Controller;

use App\Services\CartService;
use App\Repository\AddressRepository;
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
    public function index(AddressRepository $addressRepository): Response
    {

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

        // $cart_json = json_encode($cart);

        return $this->render('checkout/index.html.twig', [
            'controller_name' => 'CheckoutController',
            'cart' => $cart,
            'addresses' => $addresses,
        ]);
    }
}
