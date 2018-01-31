<?php

namespace Parametrizacao\model;

use MocaBonita\MocaBonita;
use MocaBonita\tools\validation\MbStringValidation;
use MocaBonita\tools\validation\MbValidation;
use MocaBonita\view\MbView;

/**
 * Class Parametrizacao
 * @package Parametrizacao\model
 */
class Parametrizacao
{
    /**
     * @var string
     */
    protected static $prefix = 'parametrizacao_';

    /**
     * @param array $dados
     * @return \array[]|mixed|null
     * @throws \Exception
     */
    public static function salvarParametro(array $dados)
    {
        $validation = MbValidation::validate($dados)
            ->setValidations('nome', MbStringValidation::getInstance(), ['min' => 5, 'max' => 30])
            ->setValidations('valor', ParametrizacaoValidation::getInstance());

        $validation->check(true);

        if (!update_option(self::$prefix . $validation->getData('nome'), $validation->getData('valor'))) {
            throw new \Exception("Não foi possível salvar o parametro {$validation->getData('nome')}!");
        }

        return $validation->getData('valor');
    }

    /**
     * @param $nome
     * @param null $default
     * @paran bool $valueToString
     *
     * @return string
     */
    public static function getParametro($nome, $default = null, $valueToString = false)
    {
        $value = get_option(self::$prefix . $nome, $default);

        if(is_string($value)){
            return $value;
        } elseif (is_array($value)){
            return $valueToString ? json_encode($value, JSON_PRETTY_PRINT) : $value;
        } else {
            return null;
        }
    }

    /**
     * @param array $nomes
     * @paran bool $valueToString
     *
     * @return array
     */
    public static function getParametros(array $nomes, $valueToString = false)
    {
        $dados = [];
        foreach ($nomes as $nome) {
            $dados [$nome] = self::getParametro($nome, null, $valueToString);
        }

        return $dados;
    }

    /**
     * @param array $params
     * @param bool  $valueToString
     *
     * @return MbView
     * @paran bool $valueToString
     *
     */
    public static function getMbView(array $params, $valueToString = false){
        $mbView = new MbView();

        $mbView->setMbResponse(MocaBonita::getInstance()->getMbResponse());
        $mbView->setMbRequest(MocaBonita::getInstance()->getMbRequest());
        $mbView->setTemplate('index');
        $mbView->setPage('parametrizacao');
        $mbView->setAction('index');
        $mbView->setViewPath(__DIR__ . "/../view/");

        $mbView->with('salvar', MocaBonita::getInstance()->getMbRequest()->urlAction('salvar'));
        $mbView->with('parametros', Parametrizacao::getParametros($params, $valueToString));

        return $mbView;

    }

    /**
     * @return string
     */
    public static function getPrefix()
    {
        return self::$prefix;
    }

    /**
     * @param string $prefix
     */
    public static function setPrefix($prefix)
    {
        self::$prefix = $prefix;
    }
}