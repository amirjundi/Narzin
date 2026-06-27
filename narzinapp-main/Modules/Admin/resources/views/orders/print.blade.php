<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill of Lading</title>
    <style>
        @page {
            size: A4;
            margin: 20mm;
        }

        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
            color: #333;
            line-height: 1.6;
        }

        .container {
            width: 100%;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            box-sizing: border-box;
            max-width: 800px; /* Ensuring it fits A4 size */
        }

        h1 {
            font-size: 28px;
            text-align: center;
            color: #4a90e2;
            margin-bottom: 20px;
        }

        h2 {
            font-size: 22px;
            color: #333;
            margin-bottom: 15px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }

        h3 {
            font-size: 18px;
            color: #555;
            margin-bottom: 15px;
            font-weight: bold;
        }

        p {
            font-size: 14px;
            color: #333;
            margin-bottom: 10px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table th, .table td {
            padding: 10px 15px;
            text-align: left;
            border: 1px solid #ddd;
            font-size: 14px;
        }

        .table th {
            background-color: #f0f0f0;
            color: #333;
        }

        .table td {
            background-color: #fff;
        }

        .table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .total-row td {
            font-weight: bold;
            background-color: #f0f0f0;
        }

        .total-row td:last-child {
            background-color: #fff;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }

        .footer p {
            margin: 5px 0;
        }

        .highlight {
            color: #4a90e2;
            font-weight: bold;
        }

        .address, .customer-info {
            margin-top: 20px;
        }

        .customer-info {
            font-size: 14px;
            margin-top: 15px;
        }

        /* Flex Layout for Order and Customer Information */
        .info-container {
            display: flex;
            justify-content: space-between;
            gap: 30px;
        }

        .info-container .section {
            width: 48%;
        }

        .btn-print {
            background-color: #4a90e2;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            text-align: center;
            display: block;
            width: 200px;
            margin: 20px auto;
        }

        .btn-print:hover {
            background-color: #357abd;
        }

        /* Page Breaks for Printing */
        @media print {
            .container {
                max-width: 100%;
            }

            .btn-print {
                display: none;
            }

            body {
                margin: 0;
                padding: 0;
            }

            .info-container {
                display: block;
                width: 100%;
            }

            .info-container .section {
                width: 100%;
                margin-bottom: 20px;
            }

            h1, h2, h3, p {
                page-break-before: auto;
            }

            .footer {
                font-size: 10px;
                margin-top: 30px;
            }

            .table {
                page-break-inside: auto;
            }

            .table tbody tr {
                page-break-inside: avoid;
            }

            .table thead {
                display: table-header-group;
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                width: 90%;
                padding: 10px;
            }

            h1 {
                font-size: 24px;
            }

            h2 {
                font-size: 20px;
            }

            .table th, .table td {
                font-size: 12px;
                padding: 10px 12px;
            }

            .info-container {
                flex-direction: column;
            }

            .info-container .section {
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Bill of Lading</h1>
        <h2>Order #{{ $order->order_number }}</h2>
        
        <div class="info-container">           
            <div class="section">
                <h3>Customer Information</h3>
                <p><strong>Name:</strong> {{ $order->user->name }}</p>
                <p><strong>Email:</strong> {{ $order->user->email }}</p>
                
                <div class="address">
                    <h3>Delivery Address</h3>
                    <p><strong>Location:</strong> {{ $order->address->address }}</p>
                    <p><strong>City:</strong> {{ $order->address->city }}</p>
                    <p><strong>Country:</strong> {{ $order->address->country }}</p>
                    <a href="https://maps.google.com/?q={{ $order->address->latitude }},{{ $order->address->longitude }}" 
                       target="_blank" 
                       style="color: #4a90e2; text-decoration: none;">
                        View on Map
                    </a>
                </div>
            </div>
        </div>

        <div class="order-items">
            <h3>Order Items</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Variant</th>
                        <th>Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->product->name_arabic }}</td>
                        <td>{{ $item->product_variant ? $item->productVariant->name : '-' }}</td>
                        <td>{{ $item->quantity }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} NARZIN-Commerce</p>
            <p>Thank you for your business!</p>
        </div>

        <a href="javascript:window.print();" class="btn-print">Print Bill of Lading</a>
    </div>

</body>
</html>
