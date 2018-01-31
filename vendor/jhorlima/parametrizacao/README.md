#Parametrizacao - MocaBonita

Modulo Parametrização para o MocaBonita

```sh
$ composer require jhorlima/parametrizacao
``` 

Para integrar o modulo ao plugin, basta adicionar uma controller para a Parametrizacao e depois obter a view e método de salvar.

```php
<?php

use MocaBonita\controller\MbController;
use MocaBonita\tools\MbException;
use MocaBonita\tools\MbRequest;
use MocaBonita\tools\MbResponse;
use Parametrizacao\model\Parametrizacao;

class ParametrizacaoController extends MbController
{
    /**
    * Lista de parametros que essa controller pode gerencias
    *  
    * @var string[] 
    */
    protected $parametros = [
        'nome_padrao',
        'quantidade_usuarios',
        'lista_nomes',
     ];
    
    /**
    * @inheritdoc 
    */
    public function indexAction(MbRequest $mbRequest, MbResponse $mbResponse)
    {
        return Parametrizacao::getMbView($this->parametros);
    }

    /**
    * Action para salvar parametro
 * 
    * @param MbRequest $mbRequest
    * @param MbResponse $mbResponse
    * 
    * @return MbView
    * 
    * @throws MbException
    */
    public function salvarAction(MbRequest $mbRequest, MbResponse $mbResponse)
    {
        $mbView = $this->indexAction($mbRequest, $mbResponse);

        try {
            Parametrizacao::salvarParametro($mbRequest->input());
            return $this->indexAction($mbRequest, $mbResponse);
        } catch (MbException $e) {
            $e->setExceptionData($mbView);
            throw $e;
        } catch (\Exception $e) {
            throw new MbException($e->getMessage(), $e->getCode(), $mbView);
        }
    }
}
```

Cada parametro pode ser obtido através do método 

```php
<?php

use Parametrizacao\model\Parametrizacao;

Parametrizacao::getParametro('nome_padrao'); //Obter parametro nome
Parametrizacao::getParametro('quantidade_usuarios', 10); // Obter parametro quantidade_usuarios, caso não exista, retornar 10
Parametrizacao::getParametro('lista_nomes', [], true); // Obter parametro lista_nomes, caso não exista, retornar um array vázio e depois converter-lo em JSON

```

Caso seja necessário, um parametro também pode ser criado pelo sistema. 

```php
<?php

use Parametrizacao\model\Parametrizacao;

Parametrizacao::salvarParametro([
    'nome'  => 'nome_padrao',
    'valor' => 'Jhordan Lima',
]); //Salvar parametro nome

Parametrizacao::salvarParametro([
    'nome'  => 'quantidade_usuarios',
    'valor' => 10,
]); //Salvar parametro quantidade_usuarios

Parametrizacao::salvarParametro([
    'nome'  => 'lista_nomes',
    'valor' => ['Jhordan Lima', 'Alfredo Costa', 'Antonio Iago'],
]); //Salvar parametro lista_nomes a partir de um Array

Parametrizacao::salvarParametro([
    'nome'  => 'lista_nomes',
    'valor' => "['Jhordan Lima', 'Alfredo Costa', 'Antonio Iago']",
]); //Salvar parametro lista_nomes a partir de um Json

Parametrizacao::salvarParametro([
    'nome'  => 'lista_nomes',
    'valor' => "array\nJhordan Lima\nAlfredo Costa\nAntonio Iago",
]); //Salvar parametro lista_nomes a partir de um textarea com uma lista começando por array.

```

*Obs: É possível também salvar um parametro como Array, basta envia-lo como JSON ou começar o texto com array seguido 
de um quebra de linha.
É possível definir também um outro delimitador para o array, basta criar um parametro para o atributo "**array**". 