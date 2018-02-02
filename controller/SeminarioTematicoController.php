<?php

namespace SeminarioTematico\controller;

use Illuminate\Support\Arr;
use MocaBonita\controller\MbController;
use MocaBonita\tools\MbException;
use MocaBonita\tools\MbRequest;
use MocaBonita\tools\validation\MbValidation;
use SeminarioTematico\model\Inscricao;
use SigUema\model\Usuarios;
use SigUema\service\CPFValidation;

class SeminarioTematicoController extends MbController
{

    public function indexShortcode(MbRequest $mbRequest)
    {

        try {

            if ($mbRequest->isMethod('post')):

                $count = Inscricao::count();

                if ($count <= 300) :

                    $cpf = $mbRequest->input('cpf');

                    $dadosPessoa = Usuarios::obterUsuario($cpf, $mbRequest->input('senha'));

                    $inscrito = $dadosPessoa->get('inscrito');

                    Arr::set($inscrito, 'tipo', 'inscrito');
                    Arr::set($inscrito, 'wp_user_id', 0);
                    Arr::set($inscrito, 'cpf_cnpj', $cpf);

                    $usuario = Usuarios::updateOrCreate([
                        'cpf_cnpj' => Arr::get($inscrito, 'cpf_cnpj'),
                    ], $inscrito);

                    Inscricao::updateOrCreate([
                        'usuario_id' => $usuario->getKey(),
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