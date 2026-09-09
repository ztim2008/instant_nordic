<?php

/**
 * Минимальный OpenAI-compatible клиент для DeepSeek.
 */
class nordicaiDeepSeekClient {

    private $api_key;
    private $base_url;
    private $model;
    private $timeout;

    public function __construct(array $options) {
        $this->api_key  = trim((string) ($options['deepseek_api_key'] ?? ''));
        $this->base_url = rtrim(trim((string) ($options['deepseek_base_url'] ?? 'https://api.deepseek.com')), '/');
        $this->model    = trim((string) ($options['deepseek_model'] ?? 'deepseek-chat'));
        $this->timeout  = (int) ($options['deepseek_timeout'] ?? 60);
        if ($this->timeout < 10) {
            $this->timeout = 10;
        }
    }

    public function isConfigured() {
        return $this->api_key !== '';
    }

    /**
     * @return array{ok:bool, content?:string, error?:string, raw?:array}
     */
    public function chat(array $messages, array $extra = []) {

        if (!$this->isConfigured()) {
            return ['ok' => false, 'error' => 'api_key_missing'];
        }

        $url = $this->base_url . '/chat/completions';

        $payload = array_merge([
            'model'       => $this->model,
            'messages'    => $messages,
            'temperature' => 0.2,
            'stream'      => false,
        ], $extra);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->api_key,
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 15,
        ]);

        $body = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $code  = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno) {
            return ['ok' => false, 'error' => 'curl:' . $error];
        }

        $decoded = json_decode((string) $body, true);
        if (!is_array($decoded)) {
            return ['ok' => false, 'error' => 'invalid_json_response', 'raw' => ['http' => $code, 'body' => substr((string) $body, 0, 1000)]];
        }

        if ($code >= 400) {
            $msg = $decoded['error']['message'] ?? ('http_' . $code);
            return ['ok' => false, 'error' => $msg, 'raw' => $decoded];
        }

        $content = $decoded['choices'][0]['message']['content'] ?? '';
        if ($content === '') {
            return ['ok' => false, 'error' => 'empty_content', 'raw' => $decoded];
        }

        return ['ok' => true, 'content' => $content, 'raw' => $decoded];
    }

}
