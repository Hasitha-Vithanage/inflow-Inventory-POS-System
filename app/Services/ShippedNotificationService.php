<?php

namespace App\Services;

use App\Mail\ShippedEmail;
use App\Models\NotificationLog;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\sms_gateway;
use App\utils\helpers;
use Exception;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client as Client_Twilio;
use GuzzleHttp\Client as HttpClient;

class ShippedNotificationService
{
    /**
     * Dispatch all configured shipped notifications without blocking the main thread.
     */
    public static function sendNotifications(Sale $sale)
    {
        // Load relationships needed
        $sale->load('client', 'shipping_company');

        if (!$sale->client) {
            return;
        }

        // 1. Email
        if (!empty($sale->client->email)) {
            self::sendEmail($sale);
        }

        // 2. SMS & WhatsApp
        if (!empty($sale->client->phone)) {
            $settings = Setting::first();
            $gateway = $settings && $settings->sms_gateway
                ? sms_gateway::find($settings->sms_gateway)
                : null;

            if ($gateway) {
                // Determine which SMS gateway to use based on DB settings
                self::sendSms($sale, $gateway->title);
                
                // If using Twilio, we also support WhatsApp natively
                if ($gateway->title === 'twilio') {
                    self::sendWhatsapp($sale);
                }
            }
        }
    }

    /**
     * Send HTML Email via Laravel Mailer
     */
    private static function sendEmail(Sale $sale)
    {
        try {
            Mail::to($sale->client->email)->send(new ShippedEmail($sale));
            self::log($sale->id, 'email', 'sent');
        } catch (Exception $e) {
            Log::error("Failed to send Shipped Email: " . $e->getMessage());
            self::log($sale->id, 'email', 'failed', $e->getMessage());
        }
    }

    /**
     * Send SMS using the active gateway
     */
    private static function sendSms(Sale $sale, $gatewayTitle)
    {
        $phone = $sale->client->phone;
        $message = self::buildMessageText($sale);

        try {
            if ($gatewayTitle === 'twilio') {
                $client = new Client_Twilio(env('TWILIO_SID'), env('TWILIO_TOKEN'));
                $client->messages->create($phone, [
                    'from' => env('TWILIO_FROM'),
                    'body' => $message
                ]);
            } elseif ($gatewayTitle === 'termii') {
                $client = new HttpClient();
                $client->post('https://api.ng.termii.com/api/sms/send', [
                    'json' => [
                        'to' => $phone,
                        'from' => env('TERMI_SENDER'),
                        'sms' => $message,
                        'type' => 'plain',
                        'channel' => 'generic',
                        'api_key' => env('TERMI_KEY')
                    ]
                ]);
            } elseif ($gatewayTitle === 'infobip') {
                $client = new HttpClient();
                $client->post(env('base_url') . '/sms/2/text/advanced', [
                    'headers' => [
                        'Authorization' => 'App ' . env('api_key'),
                        'Content-Type'  => 'application/json',
                        'Accept'        => 'application/json',
                    ],
                    'json' => [
                        'messages' => [
                            [
                                'from' => env('sender_from'),
                                'destinations' => [['to' => $phone]],
                                'text' => $message,
                            ]
                        ]
                    ]
                ]);
            }

            self::log($sale->id, 'sms', 'sent');
        } catch (Exception $e) {
            Log::error("Failed to send Shipped SMS: " . $e->getMessage());
            self::log($sale->id, 'sms', 'failed', $e->getMessage());
        }
    }

    /**
     * Send WhatsApp message via Twilio
     */
    private static function sendWhatsapp(Sale $sale)
    {
        // Require Twilio credentials in ENV
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_TOKEN');
        $from = env('TWILIO_FROM'); 

        // If twilio isn't fully configured, abort silently for WhatsApp
        if (!$sid || !$token) return;

        $phone = $sale->client->phone;
        // Twilio requires WhatsApp numbers to be prefixed with 'whatsapp:'
        // Ensure the phone number has a + (assuming E.164 format)
        $formattedPhone = strpos($phone, '+') === 0 ? $phone : '+' . $phone;
        $toWhatsapp = 'whatsapp:' . $formattedPhone;
        
        // Sometimes TWILIO_FROM is a regular number. For WhatsApp it must be explicitly a WhatsApp sender.
        // We'll try to use the configured TWILIO_FROM prefixed with `whatsapp:` 
        // Or default if another env var holds the specific Whatsapp sender.
        $fromWhatsapp = 'whatsapp:' . $from;

        $message = self::buildMessageText($sale);

        try {
            $client = new Client_Twilio($sid, $token);
            $client->messages->create($toWhatsapp, [
                'from' => $fromWhatsapp,
                'body' => $message
            ]);
            self::log($sale->id, 'whatsapp', 'sent');
        } catch (Exception $e) {
            Log::error("Failed to send Shipped WhatsApp: " . $e->getMessage());
            self::log($sale->id, 'whatsapp', 'failed', $e->getMessage());
        }
    }

    /**
     * Construct the plain text message body
     */
    private static function buildMessageText(Sale $sale)
    {
        $companyName = $sale->shipping_company ? $sale->shipping_company->name : 'our courier';
        $tracking = $sale->tracking_number ? $sale->tracking_number : 'Not available';

        return "Hi {$sale->client->name},\n\n"
            . "Good news! Your order {$sale->Ref} has been shipped via {$companyName}.\n"
            . "Tracking Number: {$tracking}\n\n"
            . "Thank you for shopping with us!";
    }

    /**
     * Record the notification attempt in the database
     */
    private static function log($saleId, $channel, $status, $errorMessage = null)
    {
        try {
            NotificationLog::create([
                'sale_id' => $saleId,
                'channel' => strtolower($channel),
                'status'  => strtolower($status),
                'error_message' => $errorMessage ? substr($errorMessage, 0, 1000) : null
            ]);
        } catch (Exception $e) {
            Log::error("Failed to write to notification_logs: " . $e->getMessage());
        }
    }
}
