<?php

namespace MocaBonita\tools;

use MocaBonita\model\MbSessionModel;
use Symfony\Component\HttpFoundation\Session\Session as Base;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

/**
 * Main class of the MocaBonita Session
 *
 * @author    Jhordan Lima <jhorlima@icloud.com>
 * @category  WordPress
 * @package   \MocaBonita\tools
 *
 * @copyright Jhordan Lima 2017
 * @copyright Divisão de Projetos e Desenvolvimento - DPD
 * @copyright Núcleo de Tecnologia da Informação - NTI
 * @copyright Universidade Estadual do Maranhão - UEMA
 *
 */
class MbSession extends Base
{
    /**
     * Class Instance
     *
     * @var MbSession
     */
    protected static $instance;

    /**
     * Get instance.
     *
     * @return MbSession
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            $model = new MbSessionModel;
            $storage = new NativeSessionStorage();

            $storage->setSaveHandler($model->getHandle());

            static::$instance = new static($storage);
        }

        return static::$instance;
    }
}