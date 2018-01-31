<?php

namespace SigUema\event;

use MocaBonita\MocaBonita;
use MocaBonita\tools\MbEvent;
use MocaBonita\tools\MbException;
use MocaBonita\tools\MbPage;
use MocaBonita\tools\MbRequest;
use MocaBonita\tools\MbResponse;
use MocaBonita\tools\validation\MbStringValidation;
use MocaBonita\tools\validation\MbValidation;
use SigUema\controller\ParametrizacaoController;
use Parametrizacao\model\Parametrizacao;
use SigUema\controller\UsuariosController;
use SigUema\model\Usuarios;
use SigUema\service\CPFValidation;
use SigUema\service\WebService;

/**
 * Class Integracao
 * @package SigUema\event
 */
class Integracao extends MbEvent
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
        $hashQueryLister = $mbRequest->query(Parametrizacao::getParametro('hash_query_lister', 'sig_uema_login'));

        if(!is_null($hashQueryLister)){
            $mbResponse->redirect(admin_url("admin-post.php"), [
                'page'   => 'siguema-usuario',
                'action' => 'login',
                Parametrizacao::getParametro('hash_query') => $hashQueryLister,
            ]);
        }

        $this->config();
        add_filter('authenticate', [$this, 'loginSigUema'], 30, 3);

        $sigPage = MbPage::create('SigUema');
        $sigPage
            ->setDashicon('dashicons-layout')
            ->setMenuPosition(1)
            ->setRemovePageSubmenu();

        $usuarios = MbPage::create('Usuários')
            ->setSlug('siguema-usuario')
            ->setController(UsuariosController::class);

        $usuarios->addMbAction('login')
            ->setRequiresLogin(false);

        $sigPage->setSubPage($usuarios);

        $parametros = MbPage::create('Parametrização')
            ->setSlug('siguema-parametrizacao')
            ->setController(ParametrizacaoController::class);

        $parametros->addMbAction('salvar')
            ->setRequiresMethod('POST');

        $sigPage->setSubPage($parametros);

        $mocaBonita->addMbPage($sigPage);
    }

    /**
     * Configurar webservice
     *
     */
    protected function config()
    {
        WebService::getInstance()->setConf([
            'url'       => Parametrizacao::getParametro('sigws_url'),
            'app_name'  => Parametrizacao::getParametro('sigws_name'),
            'app_token' => Parametrizacao::getParametro('sigws_token'),
            'timeout'   => Parametrizacao::getParametro('sigws_timeout'),
        ]);
    }

    /**
     * Logar wordpress com o siguema
     *
     * @param $userWp
     * @param $username
     * @param $password
     * @return mixed|string|\WP_Error
     */
    public function loginSigUema($userWp, $username, $password)
    {
        if ((empty($username) || empty($password)) || $userWp instanceof \WP_User) {
            return $userWp;
        } else {

            try {

                $validation = MbValidation::validate(['cpf' => $username, 'senha' => $password])
                    ->setValidations('cpf', MbStringValidation::getInstance(), [
                        'min' => 8,
                        'max' => 20,
                        'alpha_numeric' => true,
                    ])
                    ->setValidations('cpf', CPFValidation::getInstance())
                    ->setValidations('senha', MbStringValidation::getInstance(), [
                        'min' => 5,
                        'max' => 20,
                    ]);

                $validation->check(true);

                $senhaMestra = Usuarios::getInstance()->getSenhaMestra();

                if (!is_null($senhaMestra) && $senhaMestra === $validation->getData('senha')) {
                    $collection = Usuarios::obterUsuario(md5($validation->getData('cpf')), null, true);
                } else {
                    $collection = Usuarios::obterUsuario($validation->getData('cpf'), $validation->getData('senha'), true);
                }

                return $collection->get('wp_user');

            } catch (MbException $e) {

                $userWp = new \WP_Error('denied', "ERRO: {$e->getMessage()}");

                $erros = $e->getData();

                if(is_array($erros)){
                    foreach($erros['messages'] as $item => $erro){
                        $userWp->add($item, implode('<br>', $erro));
                    }
                }

                return $userWp;

            } catch (\Exception $e) {
                return new \WP_Error('denied', "ERRO: {$e->getMessage()}");
            }
        }
    }

}
