<?php

namespace App\Controller\Api;

use App\Repository\CarrierRepository;
use App\Services\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiCartController extends AbstractController
{
    #[Route('/api/cart/update/carrier/{id}', name: 'app_api_cart', methods: ["GET"])]
    public function index($id, CartService $cartService, CarrierRepository $carrierRepo,): Response
    {
        $carrier = $carrierRepo->findOneById($id);

        if (!$carrier) {
            return $this->json([
                "isSuccess" => false,
                "message" => "Carrier not found",
            ]);
        }
        $cartService->update("carrier", [
            "id" => $carrier->getId(),
            "name" => $carrier->getName(),
            "description" => $carrier->getDescription(),
            "price" => $carrier->getPrice(),
        ]);
        $cart = $cartService->getCartDetails();

        return $this->json([
            "isSuccess" => true,
            "data" => $cart,
        ]);
    }
}
