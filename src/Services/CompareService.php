<?php

namespace App\Services; 

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CompareService 
{

    public function __construct( private RequestStack $requestStack, private ProductRepository $productRepo,)
    {
        $this->session= $requestStack->getsession();
        $this->productRepo= $productRepo;
    }

    public function getCompare()
    {
        return $this->session->get("compare", []);
    }

    public function updateCompare($compare)
    {
        return $this->session->set("compare", $compare);
    }

    public function addToCompare($productId)
    {
        $compare= $this->getCompare();

        if (!isset($compare[$productId])){
            // product existe déjà dans compare
            $compare[$productId] = 1;
            $this->updateCompare($compare);
        }

    }

    public function removeToCompare($productId)
    {
        $compare = $this->getCompare();
        if(isset($compare[$productId])){
            unset($compare[$productId]);
            
            $this->updateCompare($compare);
        }
        
    }

    public function clearCompare()
    {
        $this->updateCompare([]);
    }

    public function getCompareDetails()
    {
        $compare = $this->getCompare();
        $result = [];
        
        foreach ($compare as $productId => $quantity) {
            $product = $this->productRepo->find($productId);
            if($product){
                
                $result[] = [
                    'id'=>$product->getId(),
                    'name'=>$product->getName(),
                    'slug'=>$product->getSlug(),
                    'imageUrls'=>$product->getImageUrls(),
                    'soldePrice'=>$product->getSoldePrice(),
                    'regularPrice'=>$product->getRegularPrice(),
                ];
               
                

            }else{
                unset($compare[$productId]);
                $this->updateCompare($compare);
            }
        }
    
        return $result;
    }
    
}