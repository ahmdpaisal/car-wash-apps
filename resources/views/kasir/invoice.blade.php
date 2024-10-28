<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $orderPayment->order_code }}</title>
    <style>
        @page {
            margin: 0;
            size: 58mm auto; /* Lebar tetap, tinggi otomatis */
        }

        /* Gaya Umum */
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
            width: 58mm;
            height: auto;
            margin: 0;
        }

        /* Header */
        .header {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 8px;
            padding: 5px 0;
            /* border-bottom: 1px solid #000; */
        }

        /* Detail Info */
        .info {
            margin: 10px 0;
            padding: 0 5px;
        }

        .info p {
            margin: 2px 0;
        }

        /* Tabel Item */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            padding: 0 5px;
        }

        .table th, .table td {
            padding: 4px;
            text-align: left;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            font-size: 11px;
        }

        .table th {
            font-weight: bold;
        }

        /* Total dan Footer */
        .total {
            font-size: 12px;
            font-weight: bold;
            text-align: right;
            margin-top: 10px;
            padding: 0 5px;
        }

        .footer {
            text-align: center;
            font-size: 10px;
            margin-top: 10px;
            padding: 0 5px;
        }

        @media print {
            .container {
                page-break-after: auto;
                page-break-inside: avoid;
            }
            .table, .table-row {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="header">INVOICE</div>

    <div class="info">
        <p><strong>No:</strong> {{ $orderPayment->order->order_code }}</p>
        <p><strong>Metode:</strong> {{ $orderPayment->payment_method }}</p>
        <p><strong>Tanggal:</strong> {{ date('d-m-Y', strtotime($orderPayment->payment_date)) }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Harga</th>
                <th>Jml</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orderPayment->order->orderDetails as $detail)
                <tr>
                    <td>{{ $detail->product_name }}</td>
                    <td>{{ 'Rp ' . number_format($detail->price) }}</td>
                    <td>{{ $detail->quantity }}</td>
                    <td>{{ 'Rp ' .number_format($detail->total_price) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
      <table style="width: 100%;">
        <tr>
          <td style="width: 50%; text-align: right;">Total: </td>
          <td style="width: 50%; text-align: right;">{{ number_format($orderPayment->bill_amount) }}</td>
        </tr>
        <tr>
          <td style="text-align: right;">Dibayar: </td>
          <td style="text-align: right;">{{ number_format($orderPayment->payment_amount) }}</td>
        </tr>
        <tr>
          <td style="text-align: right;">Kembalian: </td>
          <td style="text-align: right;">{{ number_format($orderPayment->change) }}</td>
        </tr>
      </table>
    </div>

    {{-- <div class="footer">
        <p>Thank you for your purchase!</p>
        <p>{{ now()->toDateString() }}</p>
    </div> --}}
</body>
</html>
