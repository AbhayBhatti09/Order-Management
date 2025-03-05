<!DOCTYPE html>
<html>
<head>
    <title>Order Shipped</title>
</head>
<body>
    <h1>Hello {{ $order->user->name }},</h1>
    <p>Your order (ID: {{ $order->id }}) has been shipped!</p>
    <p><strong>Order Details:</strong></p>
    <ul>
        @foreach($order->orderItems as $item)
            <li>{{ $item->product->name }} - Quantity: {{ $item->quantity }}</li>
        @endforeach
    </ul>
    <p>Thank you for shopping with us!</p>
</body>
</html>
