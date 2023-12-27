<?php

namespace App\Services; 

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService 
{

    public function __construct( private RequestStack $requestStack, private ProductRepository $productRepo,)
    {
        $this->session= $requestStack->getsession();
        $this->productRepo= $productRepo;
    }

    public function getCart()
    {
        return $this->session->get("cart", []);
    }

    public function updateCart($cart)
    {
        return $this->session->set("cart", $cart);
    }

    public function addToCart($productId, $count=1)
    {
        $cart= $this->getCart();

        if (!empty($cart[$productId])){
            // product existe déjà dans cart
            $cart[$productId] += $count;
        }else {
            // product n'est pas encore dans cart
            $cart[$productId] = $count;
        }

        $this->updateCart($cart);
    }

    public function removeToCart($productId,$count=1)
    {
        $cart = $this->getCart();
        if(isset($cart[$productId])){
            if($cart[$productId] <= $count){
                unset($cart[$productId]);
            }else{
                $cart[$productId] -= $count;
            }

            $this->updateCart($cart);
        }
        
    }

    public function clearCart()
    {
        $this->updateCart([]);
    }

    public function getCartDetails()
    {
        $cart = $this->getCart();
        $result = [
            'items' => [],
            'sub_total' => 0,
            'cart_count' => 0,
        ];
        $sub_total = 0;
        foreach ($cart as $productId => $quantity) {
            $product = $this->productRepo->find($productId);
            if($product){
                $current_sub_total = $product->getSoldePrice()*$quantity;
                $sub_total += $current_sub_total;
                $result['items'][] = [
                    'product' => [
                        'id'=>$product->getId(),
                        'name'=>$product->getName(),
                        'slug'=>$product->getSlug(),
                        'imageUrls'=>$product->getImageUrls(),
                        'soldePrice'=>$product->getSoldePrice(),
                        'regularPrice'=>$product->getRegularPrice(),
                    ],
                    'quantity' => $quantity,
                    'sub_total' => $current_sub_total,
                ];
                $result['sub_total'] = $sub_total;
                $result['cart_count'] += $quantity;
                

            }else{
                unset($cart[$productId]);
                $this->updateCart($cart);
            }
        }
    
        return $result;
    }
    
}