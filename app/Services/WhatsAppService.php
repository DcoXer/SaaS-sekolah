<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $token = '';
    private string $apiUrl = 'https://api.fonnte.com/send';

    public function __construct()
    {
        $this->token = config('services.fonnte.token') ?? '';
    }

    /**
     * Format nomor HP Indonesia ke format internasional (628xxx)
     */
    public function formatPhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        } elseif (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }

        return $phone;
    }

    /**
     * Kirim pesan teks ke WhatsApp via Fonnte
     */
    public function sendText(string $phone, string $message): array
    {
        if (empty($this->token)) {
            return ['success' => false, 'message' => 'Fonnte token belum dikonfigurasi.'];
        }

        $response = Http::timeout(30)
            ->withHeaders(['Authorization' => $this->token])
            ->post($this->apiUrl, [
                'target'  => $this->formatPhone($phone),
                'message' => $message,
            ]);

        if ($response->successful()) {
            $body = $response->json();
            if ($body['status'] ?? false) {
                return ['success' => true, 'message' => 'Pesan berhasil dikirim.'];
            }
            return ['success' => false, 'message' => $body['reason'] ?? 'Gagal mengirim pesan.'];
        }

        Log::error('Fonnte API error', ['status' => $response->status(), 'body' => $response->body()]);
        return ['success' => false, 'message' => 'Gagal terhubung ke Fonnte API.'];
    }

    /**
     * Kirim dokumen/file ke WhatsApp via Fonnte
     */
    public function sendDocument(string $phone, string $message, string $fileUrl, string $filename): array
    {
        if (empty($this->token)) {
            return ['success' => false, 'message' => 'Fonnte token belum dikonfigurasi.'];
        }

        $response = Http::timeout(30)
            ->withHeaders(['Authorization' => $this->token])
            ->post($this->apiUrl, [
                'target'   => $this->formatPhone($phone),
                'message'  => $message,
                'url'      => $fileUrl,
                'filename' => $filename,
            ]);

        if ($response->successful()) {
            $body = $response->json();
            if ($body['status'] ?? false) {
                return ['success' => true, 'message' => 'Pesan berhasil dikirim.'];
            }
            return ['success' => false, 'message' => $body['reason'] ?? 'Gagal mengirim pesan.'];
        }

        Log::error('Fonnte API error', ['status' => $response->status(), 'body' => $response->body()]);
        return ['success' => false, 'message' => 'Gagal terhubung ke Fonnte API.'];
    }
}
