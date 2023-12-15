<?php

namespace App\Controller;

use App\Services\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    public function __construct(private CartService $cartService)
    {
        $this->cartService = $cartService;
    }


    #[Route('/cart', name: 'app_cart')]
    public function index(): Response
    {
        $cart= $this->cartService->getCartDetails();
        return $this->render('cart/index.html.twig', [
            'controller_name' => 'CartController',
            'cart' => $cart 
        ]);
    }

    #[Route('/cart/add/{productId}/{count}', name: 'app_add_to_cart')]
    public function addToCart(string $productId, $count=1): Response
    {
        $this->cartService->addToCart($productId, $count);
        // dd($this->cartService->getCart());
        // dd($this->cartService->getCartDetails());

        return $this->redirectToRoute("app_cart");
    }

    #[Route('/cart/remove/{productId}/{count}', name: 'app_remove_to_cart')]
    public function removeToCart(string $productId, $count=1): Response
    {
        $this->cartService->removeToCart($productId, $count);
        return $this->redirectToRoute("app_cart");
    }
}
