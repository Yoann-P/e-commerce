<?php

namespace App\Services; 

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class WishListService 
{

    public function __construct( private RequestStack $requestStack, private ProductRepository $productRepo,)
    {
        $this->session= $requestStack->getsession();
        $this->productRepo= $productRepo;
    }

    public function getWishList()
    {
        return $this->session->get("wishlist", []);
    }

    public function updateWishList($wishlist)
    {
        return $this->session->set("wishlist", $wishlist);
    }

    public function addToWishList($productId)
    {
        $wishlist= $this->getWishList();

        if (!isset($wishlist[$productId])){
            // product existe déjà dans wishlist
            $wishlist[$productId] = 1;
            $this->updateWishList($wishlist);
        }

    }

    public function removeToWishList($productId)
    {
        $wishlist = $this->getWishList();
        if(isset($wishlist[$productId])){
            unset($wishlist[$productId]);
            
            $this->updateWishList($wishlist);
        }
        
    }

    public function clearWishList()
    {
        $this->updateWishList([]);
    }

    public function getWishListDetails()
    {
        $wishlist = $this->getWishList();
        $result = [];
        
        foreach ($wishlist as $productId => $quantity) {
            $product = $this->productRepo->find($productId);
            if($product){
                
                $result[] = [
                    'id'=>$product->getId(),
                    'name'=>$product->getName(),
                    'slug'=>$product->getSlug(),
                    'imageUrls'=>$product->getImageUrls(),
                    'soldePrice'=>$product->getSoldePrice(),
                    'regularPrice'=>$product->getRegularPrice(),
                    'stock'=>$product->getStock(),
                ];
               
                

            }else{
                unset($wishlist[$productId]);
                $this->updateWishList($wishlist);
            }
        }
    
        return $result;
    }
    
}