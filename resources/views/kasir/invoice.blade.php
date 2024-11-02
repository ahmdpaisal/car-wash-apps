<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>INV_{{ $orderPayment->order->order_code }}</title>
    <style>
        @page {
            margin: 0mm 5mm;
            size: 55mm auto; /* Lebar tetap, tinggi otomatis */
        }

        /* Gaya Umum */
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #000;
            width: 55mm;
            height: auto;
        }

        /* Header */
        .header {
            text-align: center;
            padding-top: -2px;
            /* border-bottom: 1px solid #000; */
        }

        .header img {
            width: 100px;
            height: 50px;
            margin: 0;
            padding: 0;
        }

        /* Detail Info */
        .info {
            margin: 15px 0px 5px 0px;
            padding: 0 5px;
        }

        .info p {
            margin: 2px 0;
        }

        /* Tabel Item */
        .table {
            width: 90%;
            border-collapse: collapse;
            margin-top: 5px;
            padding: 0 5px;
        }

        .table th, .table td {
            padding: 4px;
            text-align: left;
            /* border-top: 1px solid #000; */
            /* border-bottom: 1px solid #000; */
            font-size: 10px;
        }

        .table th {
            font-weight: bold;
        }

        /* Total dan Footer */
        .total {
            font-size: 10px;
            font-weight: bold;
            text-align: right;
            margin-top: 10px;
        }

        .footer {
            text-align: center;
            font-size: 9px;
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
    <div class="header">
        <img src="{{ asset('images/logo4.png') }}" alt="">
    </div>

    <div class="info">
        <p><strong>No:</strong> {{ $orderPayment->order->order_code }}</p>
        <p><strong>Metode:</strong> {{ $orderPayment->payment_method }}</p>
        <p><strong>Tanggal:</strong> {{ date('d-m-Y', strtotime($orderPayment->payment_date)) }}</p>
    </div>

    <table class="table">
        {{-- <thead>
            <tr>
                <th style="text-align: center;">Nama</th>
                <th style="text-align: center;">Harga</th>
                <th style="text-align: center;">Jml</th>
                <th style="text-align: center;">Total</th>
            </tr>
        </thead> --}}
        <tbody>
            @foreach ($orderPayment->order->orderDetails as $detail)
                <tr>
                    <td style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <span>
                                {{ $detail->product_name }}
                            </span>
                            <br>
                            <span>
                                {{ $detail->quantity . ' x ' . number_format($detail->price) }}
                            </span>
                        </div>
                        <span>
                            {{ number_format($detail->total_price) }}
                        </span>
                    </td>
                    {{-- <td style="width: 25%; text-align: center;">{{ number_format($detail->price) }}</td>
                    <td style="width: 10%; text-align: center;">{{ $detail->quantity }}</td> --}}
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
      <table style="width: 90%;">
        <tr>
          <td style="width: 60%; text-align: right;">Total: </td>
          <td style="width: 40%; text-align: left;">{{ number_format($orderPayment->bill_amount) }}</td>
        </tr>
        <tr>
          <td style="text-align: right;">Dibayar: </td>
          <td style="text-align: left;">{{ number_format($orderPayment->payment_amount) }}</td>
        </tr>
        <tr>
          <td style="text-align: right;">Kembalian: </td>
          <td style="text-align: left;">{{ number_format($orderPayment->change) }}</td>
        </tr>
      </table>
    </div>

    {{-- <META HTTP-EQUIV="REFRESH" CONTENT="2; URL={{ url('/order-payments') }}"> --}}

    <script type="text/javascript">
        window.print();
        window.onafterprint = function() {
            window.location.href = "{{ url('/order-payments') }}";
        };
    </script>
</body>
</html>
