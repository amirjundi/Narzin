<?php

namespace Modules\Checkout\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * NassPaymentService
 * 
 * Handles all communication with Nass Payment Gateway.
 * 
 * Methods:
 * - authenticate(): Get access token from Nass
 * - createTransaction(): Create a new payment transaction
 * - checkTransactionStatus(): Verify payment status with Nass
 */
class NassPaymentService
{
    private string $baseUrl;
    private ?string $accessToken = null;

    public function __construct()
    {
        $this->baseUrl = config('services.nass.base_url', 'https://uat-gateway.nass.iq:9746/');
    }

    /**
     * Authenticate with Nass and get access token
     * 
     * Flow:
     * 1. Send credentials to Nass login endpoint
     * 2. Receive access token
     * 3. Cache token for reuse (tokens typically valid for hours)
     * 
     * @return string Access token
     * @throws \Exception If authentication fails
     */
    public function authenticate(): string
    {
        try {
            // Check if we have a cached token (valid for 1 hour)
            $cachedToken = Cache::get('nass_access_token');
            if ($cachedToken) {
                $this->accessToken = $cachedToken;
                return $this->accessToken;
            }

            $response = Http::timeout(30)
                ->retry(3, 1000) // Retry 3 times with 1 second delay
                ->withOptions([
                    'verify' => app()->environment('production'), // Enable SSL for production
                    'connect_timeout' => 30,
                    'timeout' => 30,
                ])
                ->post($this->baseUrl . 'auth/merchant/login', [
                    'username' => config('services.nass.username'),
                    'password' => config('services.nass.password')
                ]);

            Log::info('Nass auth response', [
                'status' => $response->status(),
                'success' => $response->successful()
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (!isset($data['data']['access_token'])) {
                    Log::error('Access token not found in Nass response', $data);
                    throw new \Exception('Access token not found in response');
                }

                $this->accessToken = $data['data']['access_token'];
                
                // Cache token for 55 minutes (assuming 1 hour validity)
                Cache::put('nass_access_token', $this->accessToken, 3300);

                return $this->accessToken;
            }

            throw new \Exception('Authentication failed: ' . $response->status());

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Nass connection error', [
                'error' => $e->getMessage(),
                'url' => $this->baseUrl . 'auth/merchant/login'
            ]);
            throw new \Exception('Cannot connect to payment gateway: ' . $e->getMessage());

        } catch (\Exception $e) {
            Log::error('Nass authentication error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Create a new payment transaction with Nass
     * 
     * Flow:
     * 1. Ensure we have valid access token
     * 2. Send transaction details to Nass
     * 3. Receive payment URL for customer redirect
     * 
     * @param array $orderData Contains paymentId, description, amount
     * @return array Response with payment URL and transaction params
     * @throws \Exception If transaction creation fails
     */
    public function createTransaction(array $orderData, bool $retried = false): array
    {
        // Ensure we have a valid token
        if (!$this->accessToken) {
            $this->authenticate();
        }

        try {
            $response = Http::timeout(30)
                ->withOptions(['verify' => app()->environment('production')])
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => 'application/json'
                ])
                ->post($this->baseUrl . 'transaction', [
                    'orderId' => $orderData['paymentId'],
                    'orderDesc' => $orderData['description'],
                    'amount' => $orderData['amount'],
                    'currency' => '368', // Iraqi Dinar
                    'transactionType' => '1',
                    'backRef' => config('services.nass.back_ref'),
                    'notifyUrl' => config('services.nass.notify_url'),
                ]);

            Log::info('Nass transaction created', [
                'paymentId' => $orderData['paymentId'],
                'amount' => $orderData['amount'],
                'status' => $response->status()
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            // Token might be expired, try re-authenticating
            if ($response->status() === 401) {
                if ($retried) {
                    throw new \Exception('Authentication failed after retry');
                }
                Cache::forget('nass_access_token');
                $this->accessToken = null;
                $this->authenticate();
                
                // Retry the request
                return $this->createTransaction($orderData, true);
            }

            throw new \Exception('Transaction creation failed: ' . $response->status());

        } catch (\Exception $e) {
            Log::error('Nass transaction error', [
                'paymentId' => $orderData['paymentId'],
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Check transaction status with Nass
     * 
     * Flow:
     * 1. Ensure we have valid access token
     * 2. Call Nass status endpoint with orderId
     * 3. Return transaction status (approved/declined/pending)
     * 
     * Response codes:
     * - "00" = Approved (success)
     * - Other = Failed/Pending
     * 
     * @param string $orderId The payment ID sent when creating transaction
     * @return array Response with success flag and data
     */
    public function checkTransactionStatus(string $orderId, bool $retried = false): array
    {
        // Ensure we have a valid token
        if (!$this->accessToken) {
            $this->authenticate();
        }

        try {
            // Check cache first to avoid hammering Nass API
            $cacheKey = "nass_status_{$orderId}";
            $cachedStatus = Cache::get($cacheKey);
            
            if ($cachedStatus) {
                Log::info('Returning cached Nass status', ['orderId' => $orderId]);
                return $cachedStatus;
            }

            $response = Http::timeout(30)
                ->withOptions(['verify' => app()->environment('production')])
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => 'application/json'
                ])
                ->get($this->baseUrl . "transaction/{$orderId}/checkStatus");

            Log::info('Nass status check', [
                'orderId' => $orderId,
                'status' => $response->status(),
                'responseCode' => $response->json()['data']['responseCode'] ?? 'unknown'
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                // Cache successful responses for 60 seconds
                // This prevents spam and reduces Nass API load
                if (isset($result['data']['responseCode']) && $result['data']['responseCode'] === '00') {
                    Cache::put($cacheKey, $result, 60);
                }
                
                return $result;
            }

            // Token might be expired
            if ($response->status() === 401) {
                if ($retried) {
                    return [
                        'success' => false,
                        'message' => 'Authentication failed after retry',
                        'data' => null
                    ];
                }
                Cache::forget('nass_access_token');
                $this->accessToken = null;
                $this->authenticate();
                
                return $this->checkTransactionStatus($orderId, true);
            }

            return [
                'success' => false,
                'message' => 'Status check failed: ' . $response->status(),
                'data' => null
            ];

        } catch (\Exception $e) {
            Log::error('Nass status check error', [
                'orderId' => $orderId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }
}