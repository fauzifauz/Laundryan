<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Invoice {{ $order->order_code }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            line-height: 1.6;
        }

        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
        }

        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #4f46e5;
        }

        .status {
            text-transform: uppercase;
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
        }

        .status-paid {
            background: #d1fae5;
            color: #065f46;
        }

        .status-unpaid {
            background: #fee2e2;
            color: #991b1b;
        }

        table {
            width: 100%;
            line-height: inherit;
            text-align: left;
            border-collapse: collapse;
        }

        table th {
            background: #f9fafb;
            padding: 12px;
            border-bottom: 2px solid #eee;
            font-size: 14px;
        }

        table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }

        .total-row td {
            font-weight: bold;
            font-size: 16px;
            border-top: 2px solid #eee;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #999;
        }

        .info-grid {
            display: block;
            margin-bottom: 30px;
        }

        .info-col {
            width: 48%;
            display: inline-block;
            vertical-align: top;
        }
    </style>
</head>

<body>
    <div class="invoice-box">
        <div class="header">
            <span class="logo">Laundryan</span>
            <span class="status {{ $order->payment_status === 'paid' ? 'status-paid' : 'status-unpaid' }}">
                {{ $order->payment_status }}
            </span>
        </div>

        <div class="info-grid">
            <div class="info-col">
                <strong>Customer:</strong><br>
                {{ $order->customer->name }}<br>
                {{ $order->customer->email }}<br>
                {{ $order->customer->phone }}
            </div>
            <div class="info-col" style="text-align: right;">
                <strong>Order Details:</strong><br>
                Code: {{ $order->order_code }}<br>
                Date: {{ $order->created_at->format('d M Y') }}<br>
                Status: {{ str_replace('_', ' ', strtoupper($order->status)) }}
            </div>
        </div>

        <div style="margin-bottom: 30px;">
            <strong>Addresses:</strong><br>
            <small>Pickup:</small> {{ $order->pickup_address }}<br>
            <small>Delivery:</small> {{ $order->delivery_address }}
        </div>

        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="text-align: right;">Price</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Service: {{ $order->service->name }}</td>
                    <td style="text-align: right;">Rp {{ number_format($order->service_price, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Item Type: {{ $order->itemType->name }}</td>
                    <td style="text-align: right;">Rp {{ number_format($order->item_price, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Shipping Cost</td>
                    <td style="text-align: right;">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Tax
                        ({{ number_format(($order->service_price + $order->item_price + $order->shipping_cost > 0) ? ($order->tax / ($order->service_price + $order->item_price + $order->shipping_cost)) * 100 : 10, 0) }}%)
                    </td>
                    <td style="text-align: right;">Rp {{ number_format($order->tax, 0, ',', '.') }}</td>
                </tr>
                <tr class="total-row">
                    <td>TOTAL</td>
                    <td style="text-align: right;">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="footer">
            Thank you for choosing Laundryan! Your premium laundry partner.
        </div>
    </div>
</body>

</html>