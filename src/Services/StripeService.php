<?php

namespace App\Services;

use App\Repository\PaymentMethodRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class StripeService
{
    public function __construct(private RequestStack $requestStack, private PaymentMethodRepository $paymentMethodRepo,)
    {
        $this->session = $requestStack->getsession();
    }

    public function getPublicKey()
    {
        $config = $this->paymentMethodRepo->findOneByName("Stripe");
        if ($_ENV['APP_ENV'] === 'dev') {
            //developpement
            return $config->getTestPublicApiKey();
        } else {
            // production
            return $config->getProdPublicApiKey();
        };
    }
    public function getPrivateKey()
    {
        $config = $this->paymentMethodRepo->findOneByName("Stripe");
        if ($_ENV['APP_ENV'] === 'dev') {
            //developpement
            return $config->getTestPrivateApiKey();
        } else {
            // production
            return $config->getProdPrivateApiKey();
        };
    }
}
