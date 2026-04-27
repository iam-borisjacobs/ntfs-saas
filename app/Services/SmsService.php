<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Exception;

class SmsService
{
    protected $client;
    protected $fromNumber;

    public function __construct()
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $this->fromNumber = config('services.twilio.from');

        if ($sid && $token && class_exists('\Twilio\Rest\Client')) {
            $this->client = new \Twilio\Rest\Client($sid, $token);
        }
    }

    /**
     * Send an SMS message to a specific phone number.
     * 
     * @param string $toNumber
     * @param string $message
     * @return bool
     */
    public function sendSms(string $toNumber, string $message): bool
    {
        // Mock execution if credentials are not configured
        if (!$this->client || empty($this->fromNumber)) {
            Log::info("[MOCK SMS TO $toNumber]: $message");
            return true;
        }

        try {
            $this->client->messages->create($toNumber, [
                'from' => $this->fromNumber,
                'body' => $message
            ]);
            return true;
        } catch (Exception $e) {
            Log::error('Twilio SMS Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a WhatsApp message specifically for the SMS Escalation Engine.
     * 
     * @param string $toNumber
     * @param string $message
     * @return bool
     */
    public function sendWhatsApp(string $toNumber, string $message): bool
    {
        $toNumberStr = $toNumber ?: '';
        $fromNumberStr = $this->fromNumber ?: '';
        
        $whatsAppTo = str_starts_with($toNumberStr, 'whatsapp:') ? $toNumberStr : 'whatsapp:' . $toNumberStr;
        $whatsAppFrom = str_starts_with($fromNumberStr, 'whatsapp:') ? $fromNumberStr : 'whatsapp:' . $fromNumberStr;

        if (!$this->client || empty($this->fromNumber)) {
            Log::info("[MOCK WHATSAPP TO $whatsAppTo]: $message");
            return true;
        }

        try {
            $this->client->messages->create($whatsAppTo, [
                'from' => $whatsAppFrom,
                'body' => $message
            ]);
            return true;
        } catch (Exception $e) {
            Log::error('Twilio WhatsApp Error: ' . $e->getMessage());
            return false;
        }
    }
}
