<?php

namespace SigUema\event;
use MocaBonita\MocaBonita;
use MocaBonita\tools\MbPage;
use MocaBonita\tools\MbRequest;
use MocaBonita\tools\MbResponse;
use SigUema\controller\ParametrizacaoController;

/**
 * Class IntegracaoSemLogin
 * @package SigUema\event
 */
class IntegracaoSemLogin extends Integracao
{
    /**
     * Event that will start after wordpress initialize
     *
     * @param MbRequest $mbRequest
     * @param MbResponse $mbResponse
     * @param MocaBonita $mocaBonita
     */
    public function startWpDispatcher(MbRequest $mbRequest, MbResponse $mbResponse, MocaBonita $mocaBonita)
    {
        $this->config();

        $sigPage = MbPage::create('SigUema');
        $sigPage
            ->setDashicon('dashicons-layout')
            ->setMenuPosition(1)
            ->setRemovePageSubmenu();

        $parametros = MbPage::create('Parametrização')
            ->setSlug('siguema-parametrizacao')
            ->setController(ParametrizacaoController::class);

        $parametros->addMbAction('salvar')
            ->setRequiresMethod('POST');

        $sigPage->setSubPage($parametros);

        $mocaBonita->addMbPage($sigPage);
    }

}