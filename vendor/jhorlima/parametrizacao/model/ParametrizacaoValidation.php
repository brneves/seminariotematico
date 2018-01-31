<?php

namespace Parametrizacao\model;

use MocaBonita\tools\validation\MbValidationBase;

class ParametrizacaoValidation extends MbValidationBase
{

    /**
     * Implement validation
     *
     * @param mixed $value
     *
     * @param array $arguments
     *
     * @throws \Exception
     *
     * @return mixed $value
     */
    public function validate($value, array $arguments = [])
    {
        $isString = is_string($value);
        $isNumeric = is_numeric($value);
        $isArray = is_array($value);

        if ($isString) {
            $valor = explode(PHP_EOL, str_replace("\r", "", $value));

            if(count($valor) > 1 && array_shift($valor) == Parametrizacao::getParametro('array','array')){
                $value = $valor;
            } elseif ($this->isJson($value)) {
                $value = json_decode($value, true);
            }
        } elseif ($isNumeric) {
            $value = $value + 0;
        } elseif (!$isArray) {
            throw new \Exception("Não é possível armazenar este valor!");
        }

        return $value;
    }

    /**
     * Check if string is a json
     *
     * @param $string
     *
     * @return bool
     */
    protected function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}