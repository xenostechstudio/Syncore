<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #1f2937;
        }
        .header {
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 5px;
        }
        .header .subtitle {
            font-size: 11px;
            color: #6b7280;
        }
        .header .period {
            font-size: 10px;
            color: #9ca3af;
            margin-top: 5px;
        }
        .summary-grid {
            display: table;
            width: 100%;
            margin-bottom: 25px;
        }
        .summary-card {
            display: table-cell;
            width: 25%;
            padding: 10px;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
        }
        .summary-card .label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            margin-bottom: 5px;
        }
        .summary-card .value {
            font-size: 14px;
            font-weight: bold;
            color: #111827;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e5e7eb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th {
            background: #f3f4f6;
            padding: 8px 10px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            border-bottom: 1px solid #e5e7eb;
        }
        td {
            padding: 8px 10px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 10px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-medium {
            font-weight: 500;
        }
        .text-emerald {
            color: #059669;
        }
        .text-red {
            color: #dc2626;
        }
        .footer {
            position: fixed;
            bottom: 20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #9ca3af;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $companyName }}</h1>
        <div class="subtitle">Sales Report</div>
        <div class="period">
            Period: {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}
            | Generated: {{ $generatedAt->format('M d, Y H:i') }}
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="summary-grid">
        <div class="summary-card">
            <div class="label">Total Revenue</div>
            <div class="value">Rp {{ number_format($summary['total_revenue'] ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Total Orders</div>
            <div class="value">{{ number_format($summary['total_orders'] ?? 0) }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Avg Order Value</div>
            <div class="value">Rp {{ number_format($summary['avg_order_value'] ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Total Customers</div>
            <div class="value">{{ number_format($summary['total_customers'] ?? 0) }}</div>
        </div>
    </div>

    {{-- Sales by Period --}}
    @if(!empty($salesByPeriod))
    <div class="section">
        <div class="section-title">Sales by Period</div>
        <table>
            <thead>
                <tr>
                    <th>Period</th>
                    <th class="text-right">Orders</th>
                    <th class="text-right">Revenue</th>
                    <th class="text-right">Avg Value</th>
                </tr>
            </thead>
            <tbody>
                @foreach($salesByPeriod as $period)
                <tr>
                    <td>{{ $period['period'] ?? $period['date'] ?? '-' }}</td>
                    <td class="text-right">{{ number_format($period['orders'] ?? $period['count'] ?? 0) }}</td>
                    <td class="text-right">Rp {{ number_format($period['revenue'] ?? $period['total'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($period['avg_value'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Top Customers --}}
    @if(!empty($salesByCustomer))
    <div class="section">
        <div class="section-title">Top Customers</div>
        <table>
            <thead>
                <tr>
                    <th>Customer</th>
                    <th class="text-right">Orders</th>
                    <th class="text-right">Total Revenue</th>
                </tr>
            </thead>
            <tbody>
                @foreach($salesByCustomer as $customer)
                <tr>
                    <td class="font-medium">{{ $customer['name'] ?? $customer['customer_name'] ?? '-' }}</td>
                    <td class="text-right">{{ number_format($customer['orders'] ?? $customer['order_count'] ?? 0) }}</td>
                    <td class="text-right">Rp {{ number_format($customer['revenue'] ?? $customer['total'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Top Products --}}
    @if(!empty($salesByProduct))
    <div class="section">
        <div class="section-title">Top Products</div>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th class="text-right">Qty Sold</th>
                    <th class="text-right">Revenue</th>
                </tr>
            </thead>
            <tbody>
                @foreach($salesByProduct as $product)
                <tr>
                    <td class="font-medium">{{ $product['name'] ?? $product['product_name'] ?? '-' }}</td>
                    <td class="text-right">{{ number_format($product['quantity'] ?? $product['qty_sold'] ?? 0) }}</td>
                    <td class="text-right">Rp {{ number_format($product['revenue'] ?? $product['total'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Salesperson Performance --}}
    @if(!empty($salespersonPerformance))
    <div class="section">
        <div class="section-title">Salesperson Performance</div>
        <table>
            <thead>
                <tr>
                    <th>Salesperson</th>
                    <th class="text-right">Orders</th>
                    <th class="text-right">Revenue</th>
                    <th class="text-right">Avg Order</th>
                </tr>
            </thead>
            <tbody>
                @foreach($salespersonPerformance as $person)
                <tr>
                    <td class="font-medium">{{ $person['name'] ?? '-' }}</td>
                    <td class="text-right">{{ number_format($person['orders'] ?? $person['order_count'] ?? 0) }}</td>
                    <td class="text-right">Rp {{ number_format($person['revenue'] ?? $person['total'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($person['avg_order'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        {{ $companyName }} - Sales Report - Page 1
    </div>
</body>
</html>
