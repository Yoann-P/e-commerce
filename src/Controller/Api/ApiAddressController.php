<?php

namespace App\Controller\Api;

use App\Entity\Address;
use App\Repository\AddressRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api')]
class ApiAddressController extends AbstractController
{
    #[Route('/address', name: 'app_post_address', methods: ['POST'])]
    public function index(
        Request $req,
        AddressRepository $addressRepository,
        EntityManagerInterface  $manager
    ): Response {
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                "isSuccess" => false,
                "messsage" => "You are not authenticated",
                "data" => []
            ]);
        }

        $formData = $req->getPayload();

        $address = new Address();
        $address->setName($formData->get('name'))
            ->setClientName($formData->get('client_name'))
            ->setStreet($formData->get('street'))
            ->setCodePostal($formData->get('code_postal'))
            ->setCity($formData->get('city'))
            ->setState($formData->get('state'))
            ->setUser($user);
        $manager->persist($address);
        $manager->flush();

        $addresses = $addressRepository->findByUser($user);

        foreach ($addresses as $key => $address) {
            $address->setUser(null);
            $addresses[$key] = $address;
        }


        return $this->json([
            "isSuccess" => true,
            "data" => $addresses,
        ]);
    }
}
