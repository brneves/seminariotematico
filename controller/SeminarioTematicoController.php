<?php

namespace SeminarioTematico\controller;

use MocaBonita\controller\MbController;
use MocaBonita\tools\MbException;
use MocaBonita\tools\MbRequest;
use SeminarioTematico\model\Inscricao;
use SigUema\model\Usuarios;

class SeminarioTematicoController extends MbController
{

    public function indexShortcode(MbRequest $mbRequest)
    {

        try {

            if ($mbRequest->isMethod('post')):

                $count = Inscricao::count();

                if ($count <= 300) :

                    $dadosPessoa = Usuarios::obterUsuario($mbRequest->input('cpf'), $mbRequest->input('senha'), true);

                    $inscrito = Usuarios::where('wp_user_id', $dadosPessoa->get('wp_user')->ID)->firstOrFail();

                    Inscricao::updateOrCreate([
                        'usuario_id' => $inscrito->getKey(),
                    ]);

                    $this->getMbView()->setAttribute('success', 'Inscrição realizada com sucesso!');
                else :

                    throw new MbException("Limite de vagas atingido!");

                endif;

            endif;

        } catch (MbException $exception) {

            if (empty($exception->getMessages())) {
                $this->getMbView()->setAttribute('error', $exception->getMessage());
            } else {
                $this->getMbView()->setAttribute('error', $exception->getMessages());
            }

        } catch (\Exception $e) {
            $this->getMbView()->setAttribute('error', $e->getMessage());

        } finally {
            $this->getMbView()->setPage('seminario-tematico')->setAction('index');
        }

    }

}