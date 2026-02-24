<?php

namespace App\Services;

use App\Models\EmailMessage;
use App\Models\SMSMessage;
use App\Models\Sale;
use App\Models\OnlineOrder;
use App\Models\Setting;
use App\Models\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\CustomEmail;
use Twilio\Rest\Client as Client_Twilio;
use App\utils\helpers;
use App\Models\Unit;
use App\Models\Product;
use App\Models\ProductVariant;
use ArPHP\I18N\Arabic;
use PDF; // Barryvdh\DomPDF\Facade\Pdf or similar aliases

class StatusNotificationService
{
    /**
     * Dispatch an automated notification for a specific status event.
     *
     * @param string $statusEvent   e.g. 'order_placed', 'order_confirmed', 'order_packed', 'order_shipped'
     * @param int    $orderOrSaleId
     * @param string $type          'sale' (default) or 'online_order'
     * @param bool   $sendEmail     Should the system send email?
     * @param bool   $sendSms       Should the system send SMS?
     * @param bool   $sendWhatsApp  Should the system send WhatsApp?
     */
    public static function send(
        $statusEvent,
        $orderOrSaleId,
        $type = 'sale',
        $sendEmail = true,
        $sendSms = true,
        $sendWhatsApp = true
    ) {
        try {
            // Load settings
            $settings = Setting::with('Currency')->where('deleted_at', '=', null)->first();
            if (!$settings) return;

            // Load notification preferences and apply per-channel overrides
            $prefs = $settings->notification_preferences ?? [];
            $eventPrefs = $prefs[$statusEvent] ?? ['email' => true, 'sms' => true, 'whatsapp' => true];
            $sendEmail    = $sendEmail    && ($eventPrefs['email']    ?? true);
            $sendSms      = $sendSms      && ($eventPrefs['sms']      ?? true);
            $sendWhatsApp = $sendWhatsApp && ($eventPrefs['whatsapp'] ?? true);
            
            $currency_symbol = $settings->Currency ? $settings->Currency->symbol : '';
            $formatAmount = function ($amount) use ($currency_symbol) {
                return $currency_symbol . ' ' . number_format((float)$amount, 2, '.', ',');
            };

            // Load contact data
            $contactName   = '';
            $contactEmail  = '';
            $contactPhone  = '';
            $invoiceNumber = '';
            $invoiceUrl    = '';
            $totalAmount   = '';
            $paidAmount    = '';
            $dueAmount     = '';
            $businessName  = $settings->CompanyName;
            $trackingNumber = '';
            $shippingCompany = '';

            if ($type === 'sale') {
                $order = Sale::with('client', 'shippingCompany')->find($orderOrSaleId);
                if (!$order) return;

                $client = $order->client;
                if ($client) {
                    $contactName  = $client->username ?? $client->name;
                    $contactEmail = $client->email;
                    $contactPhone = $client->phone;
                }
                $invoiceNumber = $order->Ref;
                $invoiceUrl    = url('/api/sale_pdf/' . $order->id);
                $totalAmount   = $formatAmount($order->GrandTotal);
                $paidAmount    = $formatAmount($order->paid_amount);
                $dueAmount     = $formatAmount($order->GrandTotal - $order->paid_amount);
                $trackingNumber = $order->tracking_number ?? '';
                $shippingCompany = $order->shippingCompany->name ?? '';

            } elseif ($type === 'online_order') {
                $order = OnlineOrder::with('client')->find($orderOrSaleId);
                if (!$order) return;

                $client = $order->client;
                if ($client) {
                    $contactName  = $client->username ?? $client->name;
                    $contactEmail = $client->email;
                    $contactPhone = $client->phone;
                }
                $invoiceNumber = $order->ref ?? $order->Ref;
                $invoiceUrl    = url('/api/sale_pdf/' . $order->id);
                $orderTotal    = $order->total ?? $order->GrandTotal ?? 0;
                $totalAmount   = $formatAmount($orderTotal);
                $paidAmount    = $formatAmount($order->paid_amount ?? 0);
                $dueAmount     = $formatAmount($orderTotal - ($order->paid_amount ?? 0));
                $trackingNumber = $order->tracking_number ?? '';
                if (!empty($order->shipping_company_id)) {
                    $sc = \App\Models\ShippingCompany::find($order->shipping_company_id);
                    $shippingCompany = $sc->name ?? '';
                }
            } else {
                return;
            }

            // Tag replacement closure
            $replaceTags = function ($text) use (
                $contactName, $contactEmail, $contactPhone, $invoiceNumber, 
                $invoiceUrl, $totalAmount, $paidAmount, $dueAmount, 
                $businessName, $trackingNumber, $shippingCompany
            ) {
                if (empty($text)) return '';
                $text = str_replace('{contact_name}', $contactName, $text);
                $text = str_replace('{business_name}', $businessName, $text);
                $text = str_replace('{invoice_number}', $invoiceNumber, $text);
                $text = str_replace('{invoice_url}', $invoiceUrl, $text);
                $text = str_replace('{total_amount}', $totalAmount, $text);
                $text = str_replace('{paid_amount}', $paidAmount, $text);
                $text = str_replace('{due_amount}', $dueAmount, $text);
                $text = str_replace('{tracking_number}', $trackingNumber, $text);
                $text = str_replace('{shipping_company}', $shippingCompany, $text);
                return $text;
            };

            // 1. Sending Email
            if ($sendEmail && filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
                $emailTemplate = EmailMessage::where('name', $statusEvent)->first();
                if ($emailTemplate && !empty($emailTemplate->body)) {
                    $body = $replaceTags($emailTemplate->body);
                    $subject = $replaceTags($emailTemplate->subject ?? "Update regarding your order {$invoiceNumber}");
                    if (!empty($body)) {
                        $mailDetails = [
                            'body' => $body,
                            'subject' => $subject,
                            'company_name' => $businessName,
                        ];

                        // Prepare attachment
                        $attachments = [];
                        try {
                            $pdfContent = self::getInvoicePdfContent($orderOrSaleId, $type);
                            if ($pdfContent) {
                                $attachments[] = [
                                    'data' => $pdfContent,
                                    'name' => "invoice-{$invoiceNumber}.pdf",
                                    'mime' => 'application/pdf',
                                ];
                            }
                        } catch (\Throwable $pdfError) {
                            Log::error("Failed to generate PDF for attachment: " . $pdfError->getMessage());
                        }

                        try {
                            Mail::to($contactEmail)->send(new CustomEmail($mailDetails, $attachments));
                        } catch (\Exception $e) {
                            Log::error("Email [{$statusEvent}] to {$contactEmail}: " . $e->getMessage());
                        }
                    }
                }
            }

            // 2. Sending SMS
            if ($sendSms && !empty($contactPhone)) {
                $smsTemplate = SMSMessage::where('name', $statusEvent)->first();
                if ($smsTemplate && !empty($smsTemplate->text)) {
                    $smsText = $replaceTags($smsTemplate->text);
                    if (!empty($smsText)) {
                        try {
                            self::dispatchSMS($contactPhone, $smsText, $settings);
                        } catch (\Exception $e) {
                            Log::error("SMS [{$statusEvent}] to {$contactPhone}: " . $e->getMessage());
                        }
                    }
                }
            }

            // 3. Sending WhatsApp
            if ($sendWhatsApp && !empty($contactPhone)) {
                $smsTemplate = SMSMessage::where('name', $statusEvent)->first();
                if ($smsTemplate && !empty($smsTemplate->text)) {
                    $whatsappText = $replaceTags($smsTemplate->text);
                    if (!empty($whatsappText)) {
                        try {
                            self::dispatchWhatsApp($contactPhone, $whatsappText, $settings);
                        } catch (\Exception $e) {
                            Log::error("WhatsApp [{$statusEvent}] to {$contactPhone}: " . $e->getMessage());
                        }
                    }
                }
            }

        } catch (\Throwable $th) {
            Log::error("StatusNotificationService::send error: " . $th->getMessage());
        }
    }

    private static function dispatchSMS($phone, $message, Setting $settings)
    {
        $gateway = $settings->default_sms_gateway;

        $cleanPhone = preg_replace('/\s+/', '', $phone);
        if (substr($cleanPhone, 0, 1) !== '+') {
            $cleanPhone = '+' . $cleanPhone;
        }

        if ($gateway === 'twilio') {
            $env = \DB::table('sms_gateway')->where('title', 'twilio')->first();
            if ($env && !empty($env->TWILIO_SID)) {
                (new Client_Twilio($env->TWILIO_SID, $env->TWILIO_TOKEN))
                    ->messages->create($cleanPhone, [
                        'from' => $env->TWILIO_FROM,
                        'body' => $message,
                    ]);
            }
        } elseif ($gateway === 'infobip') {
            $env = \DB::table('sms_gateway')->where('title', 'infobip')->first();
            if ($env && !empty($env->api_key)) {
                (new \GuzzleHttp\Client())->request('POST', rtrim($env->base_url, '/') . '/sms/2/text/advanced', [
                    'headers' => ['Authorization' => 'App ' . $env->api_key, 'Content-Type' => 'application/json'],
                    'json'    => [
                        'messages' => [
                            [
                                'from' => $env->sender_from,
                                'destinations' => [['to' => $cleanPhone]],
                                'text' => $message
                            ]
                        ]
                    ],
                ]);
            }
        } elseif ($gateway === 'nexmo') {
            $env = \DB::table('sms_gateway')->where('title', 'nexmo')->first();
            if ($env && !empty($env->api_key)) {
                $basic = new \Vonage\Client\Credentials\Basic($env->api_key, $env->api_secret);
                $client = new \Vonage\Client($basic);
                $response = $client->sms()->send(
                    new \Vonage\SMS\Message\SMS($cleanPhone, $env->sender_from, $message)
                );
                $message = $response->current();
            }
        } elseif ($gateway === 'termii') {
             $env = \DB::table('sms_gateway')->where('title', 'termii')->first();
             if ($env && !empty($env->api_key)) {
                 (new \GuzzleHttp\Client())->request('POST', 'https://api.ng.termii.com/api/sms/send', [
                     'json' => [
                         'to' => $cleanPhone,
                         'from' => $env->sender_from,
                         'sms' => $message,
                         'type' => 'plain',
                         'channel' => 'generic',
                         'api_key' => $env->api_key,
                     ]
                 ]);
             }
        } else {
            Log::info("SMS gateway [{$gateway}] logic not fully implemented in service, falling back or skipping.");
        }
    }

    private static function dispatchWhatsApp($phone, $message, Setting $settings)
    {
        $gateway = $settings->default_sms_gateway;

        $cleanPhone = preg_replace('/\s+/', '', $phone);
        if (substr($cleanPhone, 0, 1) !== '+') {
            $cleanPhone = '+' . $cleanPhone;
        }

        if ($gateway === 'twilio') {
            $env = \DB::table('sms_gateway')->where('title', 'twilio')->first();
            if ($env && !empty($env->TWILIO_SID)) {
                (new Client_Twilio($env->TWILIO_SID, $env->TWILIO_TOKEN))
                    ->messages->create('whatsapp:' . $cleanPhone, [
                        'from' => 'whatsapp:' . $env->TWILIO_FROM,
                        'body' => $message,
                    ]);
            }
        } elseif ($gateway === 'infobip') {
            $env = \DB::table('sms_gateway')->where('title', 'infobip')->first();
            if ($env && !empty($env->api_key)) {
                (new \GuzzleHttp\Client())->request('POST', rtrim($env->base_url, '/') . '/whatsapp/1/message/text', [
                    'headers' => ['Authorization' => 'App ' . $env->api_key, 'Content-Type' => 'application/json'],
                    'json'    => ['from' => $env->sender_from, 'to' => $cleanPhone, 'content' => ['text' => $message]],
                ]);
            }
        } else {
            Log::info("WhatsApp not supported for gateway [{$gateway}]. Skipping.");
        }
    }
    /**
     * Generate PDF content for an invoice (Sale or OnlineOrder).
     * re-using logic from SalesController@Sale_PDF
     */
    public static function getInvoicePdfContent($id, $type = 'sale')
    {
        $details = [];
        $helpers = new helpers;

        if ($type === 'sale') {
            $order_data = Sale::with('details.product.unitSale')
                ->where('deleted_at', '=', null)
                ->findOrFail($id);
        } else {
            // OnlineOrder logic
            $order_data = OnlineOrder::with('items.product')->findOrFail($id);
            // We need to normalize OnlineOrder items to look like Sale details for the view
            $normalized_details = [];
            foreach ($order_data->items as $item) {
                $normalized_details[] = (object)[
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'quantity' => $item->qty,
                    'price' => $item->price,
                    'total' => $item->line_total,
                    'TaxNet' => $item->TaxNet ?? 0,
                    'discount' => $item->discount ?? 0,
                    'discount_method' => $item->discount_method ?? '2',
                    'tax_method' => $item->tax_method ?? '1',
                    'product' => $item->product,
                    'sale_unit_id' => null, // Simplified
                ];
            }
            $order_data->details = $normalized_details;
            $order_data->Ref = $order_data->ref;
            $order_data->GrandTotal = $order_data->total;
            // OnlineOrder might missing some fields expected by view, so we wrap comfortably or just use Sale if possible
        }

        $sale['client_name'] = $order_data->client->username ?? $order_data->client->name;
        $sale['client_phone'] = $order_data->client->phone;
        $sale['client_adr'] = $order_data->client->adresse ?? $order_data->shipping_address ?? '';
        $sale['client_email'] = $order_data->client->email;
        $sale['client_tax'] = $order_data->client->tax_number ?? '';
        $sale['TaxNet'] = number_format($order_data->TaxNet ?? 0, 2, '.', '');
        $sale['discount'] = number_format($order_data->discount ?? 0, 2, '.', '');
        $sale['discount_Method'] = $order_data->discount_Method ?? '2';
        $sale['discount_from_points'] = number_format($order_data->discount_from_points ?? 0, 2, '.', '');
        $sale['shipping'] = number_format($order_data->shipping ?? 0, 2, '.', '');
        $sale['statut'] = $order_data->statut ?? $order_data->status ?? '';
        $sale['Ref'] = $order_data->Ref ?? $order_data->ref;
        $sale['date'] = $order_data->date . ' ' . ($order_data->time ?? '');
        $sale['GrandTotal'] = number_format($order_data->GrandTotal, 2, '.', '');
        $sale['paid_amount'] = number_format($order_data->paid_amount ?? 0, 2, '.', '');
        $sale['due'] = number_format($sale['GrandTotal'] - $sale['paid_amount'], 2, '.', '');
        $sale['payment_status'] = $order_data->payment_statut ?? 'unpaid';

        $detail_id = 0;
        foreach ($order_data->details as $detail) {
            $unit = null;
            if (isset($detail->sale_unit_id) && $detail->sale_unit_id !== null) {
                $unit = Unit::where('id', $detail->sale_unit_id)->first();
            } else {
                $product = Product::with('unitSale')->where('id', $detail->product_id)->first();
                if ($product && $product->unitSale) {
                    $unit = $product->unitSale;
                }
            }

            if (isset($detail->product_variant_id) && $detail->product_variant_id) {
                $v = ProductVariant::find($detail->product_variant_id);
                $data['code'] = $v ? $v->code : $detail->product->code;
                $data['name'] = $v ? '[' . $v->name . ']' . $detail->product->name : $detail->product->name;
            } else {
                $data['code'] = $detail->product->code;
                $data['name'] = $detail->product->name;
            }

            $data['detail_id'] = ++$detail_id;
            $data['quantity'] = number_format($detail->quantity ?? $detail->qty, 2, '.', '');
            $data['total'] = number_format($detail->total, 2, '.', '');
            $data['unitSale'] = $unit ? $unit->ShortName : '';
            $data['price'] = number_format($detail->price, 2, '.', '');

            $disc_method = $detail->discount_method ?? '2';
            if ($disc_method == '2') {
                $data['DiscountNet'] = number_format($detail->discount, 2, '.', '');
            } else {
                $data['DiscountNet'] = number_format($detail->price * $detail->discount / 100, 2, '.', '');
            }

            $tax_rate = $detail->TaxNet ?? 0;
            $tax_price = $tax_rate * (($detail->price - $data['DiscountNet']) / 100);
            $data['Unit_price'] = number_format($detail->price, 2, '.', '');
            $data['discount'] = number_format($detail->discount, 2, '.', '');

            $tax_method = $detail->tax_method ?? '1';
            if ($tax_method == '1') {
                $data['Net_price'] = $detail->price - $data['DiscountNet'];
                $data['taxe'] = number_format($tax_price, 2, '.', '');
            } else {
                $data['Net_price'] = ($detail->price - $data['DiscountNet'] - $tax_price);
                $data['taxe'] = number_format($detail->price - $data['Net_price'] - $data['DiscountNet'], 2, '.', '');
            }

            $data['is_imei'] = $detail->product->is_imei ?? 0;
            $data['imei_number'] = $detail->imei_number ?? '';

            $details[] = $data;
        }

        $settings = Setting::where('deleted_at', '=', null)->first();
        $symbol = $helpers->Get_Currency_Code();

        $Html = view('pdf.sale_pdf', [
            'symbol' => $symbol,
            'setting' => $settings,
            'sale' => $sale,
            'details' => $details,
        ])->render();

        $arabic = new Arabic;
        $p = $arabic->arIdentify($Html);

        for ($i = count($p) - 1; $i >= 0; $i -= 2) {
            $utf8ar = $arabic->utf8Glyphs(substr($Html, $p[$i - 1], $p[$i] - $p[$i - 1]));
            $Html = substr_replace($Html, $utf8ar, $p[$i - 1], $p[$i] - $p[$i - 1]);
        }

        $pdf = PDF::loadHTML($Html);
        return $pdf->output(); // Return raw PDF data
    }
}
