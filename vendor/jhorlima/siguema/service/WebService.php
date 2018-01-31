<?php

namespace SigUema\service;

use Illuminate\Support\Arr;
use MocaBonita\tools\MbException;
use MocaBonita\tools\MbSingleton;
use \Exception;

/**
 * Class de WebService do SigUema
 *
 * @author Jhordan Lima
 * @package SigUema\service
 */
class WebService extends MbSingleton
{

    /**
     * Armazena a url da requisição
     *
     * @var string
     */
    protected $url;

    /**
     * Armazena o AppName da requisição
     *
     * @var string
     */
    protected $app_name;

    /**
     * Armazena o AppToken da requisição
     *
     * @var string
     */
    protected $app_token;

    /**
     * Armazena o Timeout da requisição
     *
     * @var int
     */
    protected $timeout;

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return WebService
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getAppName()
    {
        return $this->app_name;
    }

    /**
     * @param string $app_name
     * @return WebService
     */
    public function setAppName($app_name)
    {
        $this->app_name = $app_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getAppToken()
    {
        return $this->app_token;
    }

    /**
     * @param string $app_token
     * @return WebService
     */
    public function setAppToken($app_token)
    {
        $this->app_token = $app_token;
        return $this;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     * @return WebService
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * Método inicializador da página
     */
    protected function init()
    {
        $this->setTimeout(15);
    }

    /**
     * Definir configurações da requisição
     *
     * @param array $config
     */
    public function setConf(array $config)
    {
        foreach ($config as $key => $item) {
            $this->{$key} = $item;
        }
    }

    /**
     * Requisitar dados do siguema
     *
     * @param string $method
     * @param string $service
     * @param array $params
     * @param string $messageError
     *
     * @return mixed[]
     *
     * @throws Exception quando ocorrer algum erro na requisição
     * @throws SigUemaException quando o retorno do siguema não for válido
     */
    public function request($method, $service, array $params, $messageError = null)
    {
        $url = "{$this->url}/{$method}/{$service}/?" . http_build_query($params);

        $response = wp_remote_get($url, [
            'headers' => [
                'appName' => $this->getAppName(),
                'appToken' => $this->getAppToken(),
            ],
            'timeout' => $this->getTimeout(),
        ]);

        $messageError = is_null($messageError) ? "Ocorreu um erro ao consultar o SigUema." : $messageError;

        if ($response instanceof \WP_Error) {
            throw new MbException($response->get_error_message(), 400);
        } elseif (!$this->isJson($response['body'])) {
            throw new MbException($messageError, 400, ['data' => $response['body']]);
        }

        $body = json_decode($response['body'], true);

        $meta = Arr::get($body, 'meta', null);
        $data = Arr::get($body, 'data', null);

        if(is_null($meta)){
            throw new SigUemaException($messageError, 400);
        }

        if ($meta['code'] != 200) {
            $message = isset($meta['warning_message']) ? $meta['warning_message'] : "";
            $message = isset($meta['error_message']) ? $meta['error_message'] : $message;
            throw new SigUemaException($message, 400);
        } elseif (is_null($data)) {
            throw new SigUemaException($messageError, 400);
        }

        return $data;
    }

    /**
     * Verificar se string é array
     *
     * @param string $string
     * @return bool
     */
    protected function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

}