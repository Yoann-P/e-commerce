<?php

namespace App\Controller;

use App\Repository\PageRepository;
use App\Repository\ProductRepository;
use App\Repository\SettingRepository;
use App\Repository\SlidersRepository;
use App\Repository\CategoryRepository;
use App\Repository\CollectionsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{

    private $repoProduct;

    public function __construct(ProductRepository $repoProduct)
    {
        $this->repoProduct = $repoProduct;
    }


    #[Route('/', name: 'app_home')]
    public function index(
        SettingRepository $settingRepo,
        SlidersRepository $slidersRepo,
        CollectionsRepository $collectionsRepo,
        CategoryRepository $categoryRepo,
        PageRepository $pageRepo,
        Request $request
    ): Response {
        $session = $request->getSession();
        $data = $settingRepo->findAll();
        $sliders = $slidersRepo->findAll();
        $collections = $collectionsRepo->findBy(['isMega' => false]);
        $megaCollections = $collectionsRepo->findBy(['isMega' => true]);
        $categories = $categoryRepo->findBy(['isMega' => true]);

        // dd($data);
        $session->set('setting', $data[0]);

        $headerPages = $pageRepo->findBy(['isHead' => true]);
        $footerPages = $pageRepo->findBy(['isFoot' => true]);
        // dd($headerPages);
        // dd($footerPages);

        /* Vérifions que nous récupérons bien les produits avant de les injecter dans notre return */

        /*     $productsBestSeller = $this->repoProduct->findBy(['isBestSeller'=>true]);
        $productsNewArrival = $this->repoProduct->findBy(['isNewArrival'=>true]);
        // dd([
        //     $productsBestSeller,
        //     $productsNewArrival
        // ]);
    */

        $session->set("headerPages", $headerPages);
        $session->set("footerPages", $footerPages);
        $session->set("categories", $categories);
        $session->set("megaCollections", $megaCollections);

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'sliders' => $sliders,
            'collections' => $collections,
            'productsBestSeller' => $this->repoProduct->findBy(['isBestSeller' => true]),
            'productsNewArrival' => $this->repoProduct->findBy(['isNewArrival' => true]),

        ]);
    }

    #[Route('/product/{slug}', name: 'app_product_by_slug')]
    public function showProduct(string $slug)
    {
        $product = $this->repoProduct->findOneBy(['slug' => $slug]);

        if (!$product) {
            // rediriger sur la page error404
            return $this->redirectToRoute('app_error');
        }

        return $this->render('product/show_product_by_slug.html.twig', [
            'product' => $product
        ]);
    }

    #[Route('/product/get/{id}', name: 'app_product_by_id')]
    public function getProductById(string $id)
    {
        $product = $this->repoProduct->findOneBy(['id' =>$id]);

        if (!$product) {
            // rediriger sur la page error404
            return $this->json(false);
        }

        return $this->json([
            'id'=>$product->getId(),
                       'name' =>$product->getName(),
                       'imageUrls' =>$product->getImageUrls(),
                        'soldePrice'=>$product->getSoldePrice(),
                        'regularPrice'=>$product->getRegularPrice(),
        ]);
    }

    #[Route('/error', name: 'app_error')]
    public function erroPage()
    {
        return $this->render('page/not-found.html.twig', [
            'controller_name' => 'PageController',

        ]);
    }
}
