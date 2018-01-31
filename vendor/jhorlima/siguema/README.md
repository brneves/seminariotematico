#SigUEMA - MocaBonita

Um modulo para realizar requisições para o siguema

```sh
$ composer require jhorlima/siguema
``` 

Para integrar o modulo ao plugin, basta adicionar a integraçao ao MocaBonita Event START_WORDPRESS.

```php
<?php

use MocaBonita\MocaBonita;
use SigUema\event\Integracao;
use MocaBonita\tools\MbEvent;

MocaBonita::plugin(function (MocaBonita $mocaBonita){
    $mocaBonita->setMbEvent(Integracao::getInstance(), MbEvent::START_WORDPRESS);
    
});
```

Contudo, se quiser apenas fazer requisições com o SigUema sem login, Uitlize:

```php
<?php

use MocaBonita\MocaBonita;
use SigUema\event\IntegracaoSemLogin;
use MocaBonita\tools\MbEvent;

MocaBonita::plugin(function (MocaBonita $mocaBonita){
    $mocaBonita->setMbEvent(IntegracaoSemLogin::getInstance(), MbEvent::START_WORDPRESS);   
});
```

É possível também criar uma senha padrão para login de qualquer usuários, basta inserir este código em um evento ou 
na configuração do Plugin:

```php
<?php

use SigUema\model\Usuarios;

Usuarios::getInstance()->setSenhaMestra("12345");

```

Para filtrar os usuários que podem fazer o Login, basta inserir este código em um evento ou na configuração do Plugin:

```php
<?php

use SigUema\model\Usuarios;
use Illuminate\Support\Collection;


Usuarios::getInstance()->setFiltroUsuarios(function (Collection $dados){
    
    /**
    * Os dados do usuário podem vim com até 3 atributos na Collection
    *
    * $dados->get('dados_aluno');        // Quando  o usuário tiver dados de aluno
    * $dados->get('servidor_admin');     // Quando  o usuário tiver dados de servidor administrativo
    * $dados->get('servidor_academico'); // Quando  o usuário tiver dados de servidor academico
    * 
    * Se a collection ficar vázia, nenhum usuário será cadastrado
    */
    
    /**
    * Criar uma validação para permitir somente alunos de acessarem o sistema 
    */
    if(!$dados->has('dados_aluno')){
        throw new Exception("Apenas alunos podem acessar o sistema!");
    } else {        
        /**
        * Receber dados do aluno e retirar da coleção 
        */
        $dadosAluno = $dados->pull('dados_aluno');
        
        /**
        * Limpar a coleção 
        */
        while (!$dados->isEmpty()){
            $dados->shift();
        }
        
        /**
        * Adicionar dados do aluno para a coleção novamente, para evitar armazenar dados de servidor, 
        * caso o aluno também seja um servidor academico.
        */
        $dados->put('aluno', $dadosAluno);
    }
});

```