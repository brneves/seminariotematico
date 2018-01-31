<?php

namespace SigUema\controller;


use MocaBonita\controller\MbController;
use MocaBonita\tools\MbRequest;
use MocaBonita\tools\MbResponse;
use Parametrizacao\model\Parametrizacao;
use SigUema\model\Usuarios;

/**
 * Class UsuariosController
 * @package SigUema\controller
 */
class UsuariosController extends MbController
{
    /**
     * Example of index action
     *
     * If the return is null, then it will call MbView from this controller and then render it (Common request)
     * If the return is null, then it will print a message saying "No valid content has been submitted!" (Ajax Request)
     *
     * If the return is string, then it will print the string (Common request)
     * If the return is string, then it will add the string in the key "content" on the data (Ajax Request)
     *
     * If the return is MbView, then it will render it (Common request)
     * If the return is MbView, then it will print a message saying "No valid content has been submitted!" (Ajax Request)
     *
     * If the return is mixed, then it will call a var_dump of the returned value (Common request)
     * If the return is mixed, then it will print a message saying "No valid content has been submitted!" (Ajax Request)
     *
     * @param MbRequest $mbRequest
     * @param MbResponse $mbResponse
     *
     * @return null|string|MbView|mixed
     */
    public function indexAction(MbRequest $mbRequest, MbResponse $mbResponse)
    {

        $this->mbView->setPage('usuarios');
        $this->mbView->setAction('index');
        $this->mbView->setViewPath(__DIR__ . "/../view/");
        $this->mbView->with('usuarios', Usuarios::all());

        return $this->mbView;
    }

    /**
     * @param MbRequest $mbRequest
     * @return string
     */
    public function loginAction(MbRequest $mbRequest){
        Usuarios::logarRota($mbRequest->input(Parametrizacao::getParametro('hash_query')));

        return "Espere...";
    }

}