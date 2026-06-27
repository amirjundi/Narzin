# Checkout Flow Documentation

## Overview

This document explains how the checkout and payment flow works with the Nass payment gateway.

---

## Payment Status Values

| Status | Description |
|--------|-------------|
| `not_paid` | Order created, stock reserved, waiting for payment |
| `processing` | Payment confirmed via API check (verifyPayment) |
| `completed` | Payment confirmed via webhook (final) |
| `failed` | Payment failed |
| `expired` | User didn't pay within 15 min, stock released |

---

## Order Status Values

| Status | Description |
|--------|-------------|
| `pending_payment` | Waiting for payment |
| `confirmed` | Payment received, order confirmed |
| `processing` | Being prepared |
| `shipped` | On the way |
| `delivered` | Completed |
| `cancelled` | Cancelled by user or system |

---

## Complete Flow Diagram
```
┌─────────────────────────────────────────────────────────────────────────┐
│                           CHECKOUT FLOW                                  │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│  ┌─────────────────────────────────────────────────────────────────┐    │
│  │ 1. USER CLICKS "PLACE ORDER"                                    │    │
│  │                                                                  │    │
│  │    Frontend calls: POST /api/v1/place-order                     │    │
│  │    {                                                             │    │
│  │      address_id: 123,                                            │    │
│  │      shipping_type: "normal",                                    │    │
│  │      coupon: "SAVE10",                                           │    │
│  │      wallet: true                                                │    │
│  │    }                                                             │    │
│  └─────────────────────────────────────────────────────────────────┘    │
│                              ↓                                           │
│  ┌─────────────────────────────────────────────────────────────────┐    │
│  │ 2. BACKEND PROCESSES ORDER                                      │    │
│  │                                                                  │    │
│  │    a. Validate cart and stock                                   │    │
│  │    b. DEDUCT STOCK (reserve for this user)                      │    │
│  │    c. Create Order (payment_status: "not_paid")                 │    │
│  │    d. Create OrderItems                                         │    │
│  │    e. Clear cart                                                │    │
│  │    f. Call Nass API to create transaction                       │    │
│  │    g. Return payment URL to frontend                            │    │
│  └─────────────────────────────────────────────────────────────────┘    │
│                              ↓                                           │
│  ┌─────────────────────────────────────────────────────────────────┐    │
│  │ 3. FRONTEND REDIRECTS TO NASS                                   │    │
│  │                                                                  │    │
│  │    Creates form with transaction params                         │    │
│  │    Submits to Nass payment URL                                  │    │
│  │    User sees Nass payment page                                  │    │
│  └─────────────────────────────────────────────────────────────────┘    │
│                              ↓                                           │
│  ┌─────────────────────────────────────────────────────────────────┐    │
│  │ 4. USER PAYS ON NASS                                            │    │
│  │                                                                  │    │
│  │    User enters card details                                     │    │
│  │    Nass processes payment                                       │    │
│  │    Nass redirects to backRef (thank-you page)                   │    │
│  └─────────────────────────────────────────────────────────────────┘    │
│                              ↓                                           │
│  ┌─────────────────────────────────────────────────────────────────┐    │
│  │ 5. FRONTEND VERIFIES PAYMENT                                    │    │
│  │                                                                  │    │
│  │    Thank-you page extracts orderId from URL                     │    │
│  │    Calls: POST /api/v1/verify-payment { orderId: "123456" }     │    │
│  └─────────────────────────────────────────────────────────────────┘    │
│                              ↓                                           │
│  ┌─────────────────────────────────────────────────────────────────┐    │
│  │ 6. BACKEND VERIFIES WITH NASS                                   │    │
│  │                                                                  │    │
│  │    Calls: GET /transaction/{orderId}/checkStatus                │    │
│  │    If responseCode === "00":                                    │    │
│  │      - Update order to "processing"                             │    │
│  │      - Apply coupon usage                                       │    │
│  │      - Deduct wallet                                            │    │
│  │      - Return success to frontend                               │    │
│  └─────────────────────────────────────────────────────────────────┘    │
│                              ↓                                           │
│  ┌─────────────────────────────────────────────────────────────────┐    │
│  │ 7. WEBHOOK ARRIVES (BACKUP - 5-10 MIN LATER)                    │    │
│  │                                                                  │    │
│  │    Nass calls: POST /api/nass/webhook                           │    │
│  │    Updates order to "completed" (final confirmation)            │    │
│  └─────────────────────────────────────────────────────────────────┘    │
│                                                                          │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## Edge Cases

### 1. User Abandons Payment
```
User clicks "Place Order" → Stock reserved
User closes browser → Never pays
                    ↓
After 15 minutes:
Cron job runs → Finds unpaid order → Refills stock → Marks "expired"
```

### 2. User Pays But Redirect Fails
```
User pays on Nass → Payment successful
Browser crashes → User never reaches thank-you page
                    ↓
Webhook arrives (5-10 min later)
Backend receives webhook → Updates order to "completed"
User can see order in "My Orders"
```

### 3. User Pays at Minute 14, Cron Runs at Minute 15
```
User clicks pay at minute 14
Cron runs at minute 15 → Sees order is "not_paid" → Expires it
User completes payment → Redirected to thank-you
Frontend calls verifyPayment → Backend checks Nass → Nass says "paid"
                    ↓
Backend tries to re-reserve stock:
  - If available: Confirm order
  - If not: Refund to wallet
```

### 4. Two Users Buy Last Item
```
User A: Place Order → Stock 1 → 0 (reserved)
User B: Place Order → Stock 0 → "Insufficient stock" error

Only User A can proceed to payment
```

---

## API Endpoints

### POST /api/v1/place-order

Creates order and returns payment URL.

**Request:**
```json
{
  "address_id": 123,
  "shipping_type": "normal|fast",
  "coupon": "SAVE10",
  "wallet": true,
  "notes": "Leave at door"
}
```

**Response (Success):**
```json
{
  "status": true,
  "message": "Redirecting to payment",
  "data": {
    "order_id": 456,
    "order_number": "ORD-ABC123",
    "payment_id": "123456",
    "final_amount": 150.00,
    "payment": {
      "type": "redirect",
      "payment_url": "https://nass.iq/pay/...",
      "transaction_params": { ... }
    }
  }
}
```

### POST /api/v1/verify-payment

Verifies payment status with Nass.

**Request:**
```json
{
  "orderId": "123456"
}
```

**Response (Success):**
```json
{
  "status": true,
  "message": "Payment confirmed",
  "data": {
    "id": 456,
    "order_number": "ORD-ABC123",
    "payment_status": "processing",
    "order_status": "confirmed",
    "items": [ ... ],
    "address": { ... }
  }
}
```

### POST /api/nass/webhook

Called by Nass server (no auth required).

**Request (from Nass):**
```json
{
  "orderId": "123456",
  "responseCode": "00",
  "statusMsg": "Approved",
  "rrn": "506001398324",
  "intRef": "35E022E154BEDD46"
}
```

**Response:**
```json
{
  "status": "ok"
}
```

---

## Cron Job

**Command:** `php artisan orders:release-expired`

**Schedule:** Every 5 minutes

**What it does:**
1. Finds orders with `payment_status` = "not_paid" or "failed"
2. Where `created_at` < 15 minutes ago
3. For each order:
   - Refill stock for all items
   - Set `payment_status` = "expired"
   - Set `order_status` = "cancelled"

---

## Database Changes

Add these columns to the `orders` table:
```sql
ALTER TABLE orders ADD COLUMN coupon_applied_at TIMESTAMP NULL;
ALTER TABLE orders ADD COLUMN wallet_deducted_at TIMESTAMP NULL;
```

These columns prevent double application of coupon/wallet when both
verifyPayment and webhook process the same order.

---

## Configuration

Add to `config/services.php`:
```php
'nass' => [
    'username' => env('NASS_USERNAME'),
    'password' => env('NASS_PASSWORD'),
    'back_ref' => env('NASS_BACK_REF', 'https://narzin.com/thank-you'),
    'notify_url' => env('NASS_NOTIFY_URL', 'https://admin.narzin.com/api/nass/webhook'),
],
```

Add to `.env`:
```
NASS_USERNAME=Admin@narzin.com
NASS_PASSWORD=your_password
NASS_BACK_REF=https://narzin.com/thank-you
NASS_NOTIFY_URL=https://admin.narzin.com/api/nass/webhook
```