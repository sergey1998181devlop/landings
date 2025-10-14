<?php

namespace ADDONS\DOP;

use Simpla;

abstract class DOPApi extends Simpla
{
    protected const LOG_FILENAME = 'dop_api.txt';
    protected const TIMEOUT = 10;

    /**
     * Возвращает список доменов API
     *
     * @return string[]
     */
    abstract protected function domains(): array;

    /**
     * Возвращает API ключ
     *
     * @return string
     */
    abstract protected function apiKey(): string;

    /**
     * Генерация ключа и получение всех данных лицензии
     * 
     * @param array $data Данные для генерации ключа
     * @return array Массив с данными лицензии или response в случае ошибки
     */
    public function makeKey(array $data)
    {
        $response = $this->executeRequest(
            '/license/makeKey',
            [
                'method' => 'POST',
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode($data),
            ]
        );

        if (isset($response['result'], $response['ok']) && $response['ok']) {
            return $response['result'];
        }
        
        return $response;
    }

    /**
     * Выполняет HTTP запрос к API с поддержкой резервных доменов
     *
     * @param string $endpoint Конечная точка API
     * @param array $options Параметры запроса
     * @return array|false
     */
    protected function executeRequest(string $endpoint, array $options = [])
    {
        $options['headers']['apikey'] = $this->apiKey();

        foreach ($this->domains() as $domain) {
            $url = $domain . $endpoint;
            $response = $this->sendRequest($url, $options);
            
            if ($response !== false) {
                return $response;
            }
        }

        return false;
    }

    /**
     * Отправляет единичный HTTP запрос
     * 
     * @param string $url URL для запроса
     * @param array $options Параметры запроса
     * @return array|false
     */
    private function sendRequest(string $url, array $options)
    {
        $ch = $this->initCurl($url, $options);
        
        if ($ch === false) {
            return false;
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);

        $this->logging(
            __METHOD__,
            $url,
            $options,
            [
                'http_code' => $httpCode,
                'response' => $response,
                'error' => $error,
            ],
            static::LOG_FILENAME
        );

        if ($httpCode === 200 && $response !== false) {
            $decoded = json_decode($response, true);
            return $decoded ?: false;
        }

        return false;
    }

    /**
     * Инициализирует CURL с общими параметрами
     * 
     * @param string $url
     * @param array $options
     * @return resource|false
     */
    private function initCurl(string $url, array $options)
    {
        $ch = curl_init($url);
        
        if ($ch === false) {
            return false;
        }
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->formatHeaders($options['headers'] ?? []));

        if (!empty($options['method']) && strtolower($options['method']) === 'post') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $options['body'] ?? '');
        }

        return $ch;
    }

    /**
     * Форматирует заголовки для CURL
     *
     * @param array $headers
     * @return string[]
     */
    private function formatHeaders(array $headers): array
    {
        return array_map(
            static fn($key, $value) => "$key: $value",
            array_keys($headers),
            $headers
        );
    }
}
