<?php

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require_once 'Simpla.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';

class TBankIdService extends Simpla
{
    private $url_auth_token;
    private $url_userinfo;
    private $url_inn;
    private $url_documents_passport;
    private $url_address;
    private $client_id;
    private $client_secret;
    private $redirect_uri;
    private $token;
    private Client $httpClient;


    public function __construct()
    {
        parent::__construct();

        $this->initHttpClient();

        $this->url_auth_token = 'https://id.tbank.ru/auth/token';
        $this->url_userinfo = 'https://id.tbank.ru/userinfo/userinfo';
        $this->url_inn = 'https://business.tbank.ru/openapi/api/v1/individual/documents/inn';
        $this->url_documents_passport = 'https://business.tbank.ru/openapi/api/v1/individual/documents/passport';
        $this->url_address = 'https://business.tbank.ru/openapi/api/v1/individual/addresses';

        $this->client_id = $this->config->TBankId['clientId'];
        $this->client_secret = $this->config->TBankId['clientSecret'];
        $this->redirect_uri = $this->config->root_url . '/t-bank-id/auth';
    }

    /**
     * Генерация UID state
     * @return string
     */
    private function generateUuid(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Сохраним уникальный state в сессию для дальнейшей проверки
     * @return string
     */
    public function setState(): string
    {
        $token = $this->generateUuid();
        setcookie('t_id_state', $token, time() + 60 * 5, '/', $this->config->main_domain);
        return $token;
    }

    /**
     * Валидирует state
     *
     * @param string $receivedState
     * @return bool
     */
    public function validateState(string $receivedState): bool
    {
        return $_COOKIE['t_id_state'] === $receivedState;
    }

    /**
     * @return void
     */
    private function initHttpClient()
    {
        // Настройка логгера
        $logger = new Logger('guzzle');
        $logger->pushHandler(new StreamHandler($this->config->root_dir . '/logs/t_bank_id_logs.txt', Logger::INFO));

        // Создание стека обработчиков
        $stack = HandlerStack::create();
        $stack->push(Middleware::log($logger, new MessageFormatter('Request: {request} - Response: {response}')));

        $this->httpClient = new Client(
            [
                'handler' => $stack,
            ]
        );
    }


    /**
     * Получает токен пользователя для запросов в АПИ
     * @param $code
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getToken($code)
    {
        $response = $this->httpClient->post($this->url_auth_token, [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->client_id . ':' . $this->client_secret),
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $this->redirect_uri,
            ],
        ]);

        // Декодируем JSON-ответ (если API возвращает JSON)
        return json_decode($response->getBody(), true); // $token['access_token']
    }

    /**
     * Устанавливает токен для пользователя
     * @param string $token
     * @return void
     */
    public function setUserToken(string $token)
    {
        $this->token = $token;
    }

    /**
     * @return void
     */
    public function setCookie()
    {
        $amount = $this->request->get('amount');
        $period = $this->request->get('period');

        setcookie("amount", $amount, time() + 3600);
        setcookie("period", $period, time() + 3600);
    }

    /**
     * Получает ФИО, телефон пользователя
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getMainData()
    {

        $response = $this->httpClient->post($this->url_userinfo, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
            ],
        ]);

        return json_decode($response->getBody(), true); // $response['access_token']
    }

    /**
     * Получает ИНН пользователя
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getInn()
    {
        $response = $this->httpClient->get($this->url_inn, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody(), true); // $response['inn']
    }

    /**
     * Получаем адрес пользователя
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getAddresses()
    {
        $response = $this->httpClient->get($this->url_address, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * Паспортные данные
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getPassportData()
    {
        $response = $this->httpClient->get($this->url_documents_passport, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody(), true);
    }
}
