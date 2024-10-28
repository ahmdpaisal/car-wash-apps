<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderPayment;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrderPaymentController extends Controller
{
    
    public function invoicePDF($record)
    {
        $record = base64_decode($record);
        $order = Order::where('order_code', $record)->first();
        $orderPayment = OrderPayment::where('order_id', $order->id)->first();
        $orderPayment->load('order.orderDetails');

        //Use DomPDF
        return $this->configDomPDF($order->order_code, $orderPayment);

        //Use Snappy
        // return $this->configSnappy($order->order_code, $orderPayment);

        
    }

    protected function configDomPDF($orderCode, $orderPayment){
        // Konfigurasi DomPDF
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('isJavascriptEnabled', true);

        $dompdf = new Dompdf($options);

        // Render view menjadi HTML
        $html = view('kasir.invoice', ['orderPayment' => $orderPayment]);

        // Load HTML ke DomPDF
        $dompdf->loadHtml($html);
        // Tetapkan lebar kertas 58mm dan tinggi halaman otomatis
        $width = 58 * 2.83465;
        $height = 297 * 2.83465;
        $customPaper = array(0, 0, $width, $height); // Menetapkan tinggi maksimal yang cukup besar
        $dompdf->setPaper($customPaper, 'portrait');
        
        $dompdf->render();
        
        // Mengembalikan response sebagai PDF
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="invoice_' . $orderCode . '.pdf"',
        ]);
    }

    protected function configSnappy($orderCode, $orderPayment){
        $pdf = SnappyPdf::loadView('kasir.invoice', compact('orderPayment'))
                ->setOption('page-width', '58mm')
                ->setOption('page-height', '297mm')
                ->setOption('disable-smart-shrinking', true) // Mencegah penyusutan otomatis
                ->setOption('margin-top', '0')
                ->setOption('margin-right', '0')
                ->setOption('margin-bottom', '0')
                ->setOption('margin-left', '0')
                ->setOption('orientation', 'Portrait');

        return response($pdf->inline("invoice_{$orderCode}.pdf"))
            ->header('Content-Type', 'application/pdf');
    }

}
