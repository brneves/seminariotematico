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

                $count = Usuarios::count();

                if (true) :

                    $cpf = $mbRequest->input('cpf');

                    $dadosPessoa = Usuarios::obterUsuario($cpf, $mbRequest->input('senha'));

                    $inscrito = $dadosPessoa->first();

                    Arr::set($inscrito, 'wp_user_id', 1);
                    Arr::set($inscrito, 'cpf_cnpj', $cpf);

                    Usuarios::updateOrCreate([
                        'cpf_cnpj' => $inscrito['cpf_cnpj'],
                    ], $inscrito);

                    $this->getMbView()->setAttribute('success', 'Inscrição realizada com sucesso!');
                    $this->getMbView()->setAttribute('inscrito', $inscrito);
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

    public function seminarioShortcode(MbRequest $mbRequest)
    {

    }

    public function salvarAction(MbRequest $mbRequest)
    {

        if (!$mbRequest->hasHeader('migracao-usuario'))
            throw new \Exception("Sem permissão");

        $count = Usuarios::count();

        if (true) :

            $cpf = (int) $mbRequest->input('cpf');

            $dadosPessoa = Usuarios::obterUsuario(md5($cpf));

            $inscrito = $dadosPessoa->first();

            Arr::set($inscrito, 'wp_user_id', 1);
            Arr::set($inscrito, 'cpf_cnpj', $cpf);

            return Usuarios::updateOrCreate([
                'cpf_cnpj' => $inscrito['cpf_cnpj'],
            ], $inscrito);

        else :

            throw new MbException("Limite de vagas atingido!");

        endif;

    }


}