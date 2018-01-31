<?php

namespace SigUema\service;

use MocaBonita\tools\validation\MbValidationBase;

/**
 * Class CPFValidation
 * @package SigUema\service
 */
class CPFValidation extends MbValidationBase
{

    /**
     *
     * Validar CPF
     *
     * @param mixed $value
     * @param array $arguments
     * @return int
     * @throws \Exception
     */
    public function validate($value, array $arguments = [])
    {
        if(!is_numeric($value) && is_string($value)){
            throw new \Exception("CPF Inválido!");
        }

        $value = preg_replace('/[^0-9]/', '', (string) $value);

        $value = str_pad($value, 11, "0", STR_PAD_LEFT);

        $invalidos = [
            '00000000000',
            '11111111111',
            '22222222222',
            '33333333333',
            '44444444444',
            '55555555555',
            '66666666666',
            '77777777777',
            '88888888888',
            '99999999999',
        ];
        // Valida tamanho
        if (strlen($value) > 11)
            throw new \Exception("CPF Inválido! Número de caracteres diferente de 11.");
        elseif (in_array($value, $invalidos))
            throw new \Exception("CPF Inválido! CPF está na lista de CPFs ignorados.");
        // Calcula e confere primeiro dígito verificador
        for ($i = 0, $j = 10, $soma = 0; $i < 9; $i++, $j--)
            $soma += $value[$i] * $j;
        $resto = $soma % 11;
        if ($value[9] != ($resto < 2 ? 0 : 11 - $resto))
            throw new \Exception("CPF Inválido!");
        // Calcula e confere segundo dígito verificador
        for ($i = 0, $j = 11, $soma = 0; $i < 10; $i++, $j--)
            $soma += $value[$i] * $j;
        $resto = $soma % 11;
        if($value[10] == ($resto < 2 ? 0 : 11 - $resto))
            return (int) $value;
        throw new \Exception("CPF Inválido!");
    }
}