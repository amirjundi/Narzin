<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders PDF</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: #f0f0f0;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
        }

        .total {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Orders List</h1>
        <table>
            <thead>
                <tr>
                    <th>Order Number</th>
                    <th>Username</th>
                    <th>Address</th>
                    <th>Status</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->user->name }}</td>
                        <td>{{ $order->address->address }}</td>
                        @if(isset($order->status))
                            <td>{{ $order->status->name }}</td>
                        @endif
                        <td>${{ number_format($order->total_amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
