<?php

/**
 * Plugin Name: Seminário Temático da Graduação
 * Plugin URI: http://exemplo.plugin.com
 * Description: Plugin para controle das inscrições no Seminário Temático da Graduação
 * Version: 1.0.0
 * Author: Jhordan Lima e Ronaldo Neves
 * Author URI: http://www.github.com/fulando
 * License: GLPU
 *
 * @doc: https://developer.wordpress.org/plugins/the-basics/header-requirements/
 */

namespace SeminarioTematico;

use Illuminate\Support\Collection;
use MocaBonita\MocaBonita;
use MocaBonita\tools\MbEvent;
use MocaBonita\tools\MbException;
use MocaBonita\tools\MbPage;
use MocaBonita\tools\MbPath;
use SeminarioTematico\controller\SeminarioGraduacaoController;
use SeminarioTematico\controller\SeminarioTematicoController;
use SeminarioTematico\model\Inscricao;
use SigUema\event\Integracao;
use SigUema\model\Usuarios;

if (!defined('ABSPATH')) {
    die('Acesso negado!' . PHP_EOL);
}

$pluginPath = plugin_dir_path(__FILE__);
$loader = require $pluginPath . "vendor/autoload.php";
$loader->addPsr4(__NAMESPACE__ . '\\', $pluginPath);

/**
 * Callback que será chamado ao ativar o plugin (Opicional)
 * @doc: https://jhorlima.github.io/wp-mocabonita/classes/MocaBonita.MocaBonita.html#method_active
 */
MocaBonita::active(function (MocaBonita $mocabonita) {

    Usuarios::createSchemaModel();
    Inscricao::createSchemaModel();

});


/**
 * Callback que terão as configurações do plugin
 * @doc: https://jhorlima.github.io/wp-mocabonita/classes/MocaBonita.MocaBonita.html#method_plugin
 */
MocaBonita::plugin(function (MocaBonita $mocabonita) {

    $mocabonita->setMbEvent(Integracao::getInstance(), MbEvent::START_WORDPRESS);

    Usuarios::getInstance()->setFiltroUsuarios(function (Collection $collection) {

        $collection->each(function ($dados, $tipo) use ($collection) {

            if (is_null($dados)) {
                $collection->offsetUnset($tipo);
            } else {
                $collection->put($tipo, $dados + ['tipo' => $tipo]);
            }

        });

        if ($collection->isEmpty()):
            throw new MbException("Usuário inválido");
        endif;
    });

    /**
     * Criando uma página para o Plugin
     */
    $seminatioTematico = MbPage::create('Seminário Temático');

    /**
     * Aqui podemos configurar alguns ajustes da página
     * @doc: https://jhorlima.github.io/wp-mocabonita/classes/MocaBonita.tools.MbPage.html
     */
    $seminatioTematico->setMenuPosition(1)
        ->setDashicon('dashicons-admin-site')
        ->setRemovePageSubmenu()
        ->setSlug('seminario-tematico')
        ->setController(SeminarioTematicoController::class);

    $seminatioTematico->addMbAction('salvar')
        ->setRequiresMethod('POST')
        ->setRequiresLogin(false)
        ->setRequiresAjax(true);

    /**
     * Caso seu plugin precise de um shortcode, você pdoe adiciona-lo associando à página.
     * Seu comportamento é semelhante a de uma action, contudo seu sufixo deve ser "Shortcode", Ex: exemploShortcode(array $attributes, $content, $tags).
     * @doc: https://codex.wordpress.org/Shortcode_API
     * @doc: https://jhorlima.github.io/wp-mocabonita/classes/MocaBonita.MocaBonita.html#method_addMbShortcode
     */
    $mocabonita->addMbShortcode('seminario_tematico', $seminatioTematico, 'index');


    $seminarioGraduacao = MbPage::create('Seminário Caminhos da Graduação');

    $seminarioGraduacao->setMenuPosition(2)
        ->setRemovePageSubmenu()
        ->setSlug('seminario-graduacao')
        ->setController(SeminarioGraduacaoController::class);

    $seminarioGraduacao->addMbAction('salvar')
        ->setRequiresMethod('POST')
        ->setRequiresLogin(false)
        ->setRequiresAjax(true);

    $mocabonita->addMbShortcode('seminario_caminhos', $seminarioGraduacao, 'index');

    /**
     * Após finalizar todas as configurações da página, podemos adiciona-las ao MocaBonita para que elas possam ser
     * usadas pelo Wordpress. Caso uma página não seja adicionada, apenas os shortcodes relacionados a ela serão
     * executados.
     */
    $mocabonita->addMbPage($seminatioTematico);
    $mocabonita->addMbPage($seminarioGraduacao);

    $mocabonita->getAssetsPlugin()
        ->setCss(MbPath::pCssDir('bootstrap.min.css'));

});