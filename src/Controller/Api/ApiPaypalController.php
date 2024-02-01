<?php

namespace App\Controller\Api;

use Exception;
use App\Services\PaypalService;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiPaypalController extends AbstractController
{

    public function __construct(
        PaypalService $paypalService,
        private HttpClientInterface $client
    ) {
        $this->paypalService = $paypalService;
        $this->paypal_public_key = $paypalService->getPublicKey();
        $this->paypal_private_key = $paypalService->getPrivateKey();
        $this->base  = $this->paypalService->getBaseUrl();
    }

    #[Route('/api/paypal/orders', name: 'app_create_paypal', methods: ['POST'])]
    public function index(
        Request $req,
        OrderRepository $orderRepo,
        EntityManagerInterface $em
    ): Response {

        $orderId = $req->getPayload()->get("orderId");

        $order = $orderRepo->findOneById($orderId);

        if (!$order) {
            return $this->json(["error" => 'Order not found']);
        }

        $result = $this->createOrder($order);

        if (isset($result['jsonResponse']['id'])) {
            $id = $result['jsonResponse']['id'];
            $order->setpaypalClientSecret($id);
            $em->persist($order);
            $em->flush();
        }

        return $this->json($result['jsonResponse']);
    }

    #[Route('/api/orders/{orderID}/capture', name: 'app_capture_paypal', methods: ['POST'])]
    public function capturePaiement(
        $orderID,
        Request $req,
        OrderRepository $orderRepo,
        EntityManagerInterface $em
    ) {
        try {
            //code...
            $result = $this->captureOrder($orderID);

            if (isset($result['jsonResponse']['id']) && isset($result['jsonResponse']['status'])) {
                $id = $result['jsonResponse']['id'];
                $status = $result['jsonResponse']['status'];
                if ($status === "COMPLETED") {
                    $order = $orderRepo->findOneByPaypalClientSecret($id);
                    if ($order) {
                        $order->setIsPaid(true);
                        $order->setPaymentMethod("PAYPAL");
                        $em->persist($order);
                        $em->flush();
                    }
                }
            }

            return $this->json($result['jsonResponse']);
        } catch (Exception $error) {

            error_log("Failed to capture order:" . $error->getMessage());
            return $this->json(["error" => "Failed to capture order."], 500);
        }
    }

    public function generateAccessToken()
    {
        try {
            //code...
            if (empty($this->paypal_public_key) || empty($this->paypal_private_key)) {
                throw new Exception("MISSING_API_CREDENTIALS");
            }

            $auth = base64_encode($this->paypal_public_key . ":" . $this->paypal_private_key);


            $response = $this->client->request(
                'POST',
                $this->base . '/v1/oauth2/token',
                [
                    'body' => "grant_type=client_credentials",
                    'headers' => ['Authorization' => "Basic " . $auth]
                ]
            );

            $data = $response->toArray();
            return $data['access_token'];
        } catch (Exception $th) {
            //throw $th;
            return null;
        }
    }

    /**
     * Create an order to start the transaction.
     * @see https://developer.paypal.com/docs/api/orders/v2/#orders_create
     */
    public function createOrder($order)
    {


        // Use the cart information passed from the front-end to calculate the purchase unit details
        // error_log("Shopping cart information passed from the frontend createOrder() callback: " . json_encode($cart));

        $accessToken = $this->generateAccessToken();
        $url = $this->base . '/v2/checkout/orders';
        $payload = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => 'EUR',
                        'value' => $order->getOrderCostTtc() / 100,
                    ],
                ],
            ],
        ];


        $response = $this->client->request('POST', $url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
                // Uncomment one of these to force an error for negative testing (in sandbox mode only).
                // Documentation: https://developer.paypal.com/tools/sandbox/negative-testing/request-headers/
                // 'PayPal-Mock-Response' => '{"mock_application_codes": "MISSING_REQUIRED_PARAMETER"}',
                // 'PayPal-Mock-Response' => '{"mock_application_codes": "PERMISSION_DENIED"}',
                // 'PayPal-Mock-Response' => '{"mock_application_codes": "INTERNAL_SERVER_ERROR"}',
            ],
            'json' => $payload,
        ]);

        return $this->handleResponse($response);
    }

    /**
     * Capture payment for the created order to complete the transaction.
     * @see https://developer.paypal.com/docs/api/orders/v2/#orders_capture
     */
    public function captureOrder($orderID)
    {

        $accessToken = $this->generateAccessToken();
        $url = $this->base . '/v2/checkout/orders/' . $orderID . '/capture';


        $response = $this->client->request('POST', $url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
                // Uncomment one of these to force an error for negative testing (in sandbox mode only).
                // Documentation: https://developer.paypal.com/tools/sandbox/negative-testing/request-headers/
                // 'PayPal-Mock-Response' => '{"mock_application_codes": "INSTRUMENT_DECLINED"}',
                // 'PayPal-Mock-Response' => '{"mock_application_codes": "TRANSACTION_REFUSED"}',
                // 'PayPal-Mock-Response' => '{"mock_application_codes": "INTERNAL_SERVER_ERROR"}',
            ],
        ]);

        return $this->handleResponse($response);
    }

    public function handleResponse($response)
    {
        try {
            $jsonResponse = json_decode($response->getContent(), true);
            return [
                'jsonResponse' => $jsonResponse,
                'httpStatusCode' => $response->getStatusCode(),
            ];
        } catch (Exception $error) {
            $errorMessage = (string) $response->getContent();
            throw new Exception($errorMessage);
        }
    }
}
