<?php

namespace MocaBonita\tools;

use MocaBonita\MocaBonita;
use MocaBonita\view\MbView;

/**
 * Main class of the MocaBonita Shortcode
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
class MbShortCode
{

    /**
     * Shortcode name
     *
     * @var string
     */
    private $name;

    /**
     * Shortcode MbAction
     *
     * @var MbAction
     */
    private $mbAction;

    /**
     * Shortcode MbAsset
     *
     * @var MbAsset
     */
    private $mbAsset;

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return MbShortCode
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get MbAction
     *
     * @return MbAction
     */
    public function getMbAction()
    {
        return $this->mbAction;
    }

    /**
     * Set MbAction
     *
     * @param MbAction $mbAction
     *
     * @return MbShortCode
     */
    public function setMbAction(MbAction $mbAction)
    {
        $this->mbAction = $mbAction;

        return $this;
    }

    /**
     * Get MbAsset
     *
     * @return MbAsset
     */
    public function getMbAsset()
    {
        return $this->mbAsset;
    }

    /**
     * Set MbAsset
     *
     * @param MbAsset $mbAsset
     *
     * @return MbShortCode
     */
    public function setMbAsset(MbAsset $mbAsset)
    {
        $this->mbAsset = $mbAsset;

        return $this;
    }

    /**
     * Shortcode construct
     *
     * @param string   $name nome do shortcode
     * @param MbAction $mbAction
     * @param MbAsset  $mbAsset
     */
    public function __construct($name, MbAction $mbAction, MbAsset $mbAsset)
    {
        $this->setName($name)
            ->setMbAction($mbAction)
            ->setMbAsset($mbAsset);
    }

    /**
     * Run shortcode
     *
     * @param MbAsset    $mbAsset
     *
     * @param MbRequest  $mbRequest
     *
     * @param MbResponse $mbResponse
     */
    public function runShortcode(MbAsset $mbAsset, MbRequest $mbRequest, MbResponse $mbResponse)
    {
        $shortCode = $this;

        //Initialize Shorcode
        add_shortcode($this->getName(),

            function ($attributes, $content, $tags) use ($shortCode, $mbAsset, $mbRequest, $mbResponse) {

                MbEvent::callEvents(MocaBonita::getInstance(), MbEvent::BEFORE_SHORTCODE, $shortCode);

                $mbRequest->setMbPage($shortCode->getMbAction()->getMbPage());

                $mbRequest->setShortcode(true);

                //Add plugin assets
                $mbAsset->setActionEnqueue('front')
                    ->runAssets('plugin', true);

                //Add page assets
                $mbRequest->getMbPage()
                    ->getMbAsset()
                    ->setActionEnqueue('front')
                    ->runAssets($shortCode->getName(), true);

                //Add shortcode assets
                $shortCode->getMbAsset()
                    ->setActionEnqueue('front')
                    ->runAssets($shortCode->getName(), true);


                try {

                    try {
                        ob_start();

                        MbEvent::callEvents(
                            MocaBonita::getInstance(),
                            MbEvent::BEFORE_ACTION,
                            $shortCode->getMbAction()
                        );

                        $mbView = new MbView();

                        $mbView->setMbRequest($mbRequest)
                            ->setMbResponse($mbResponse)
                            ->setView('shortcode', 'shortcode', $shortCode->getMbAction()->getName());

                        $actionResponse = MocaBonita::getInstance()->runAction($shortCode->getMbAction(), $mbView, [
                            $mbRequest,
                            $mbResponse,
                            [
                                'attributes' => $attributes,
                                'content'    => $content,
                                'tags'       => $tags,
                            ],
                            $mbView,
                        ]);

                        MbEvent::callEvents(
                            MocaBonita::getInstance(),
                            MbEvent::AFTER_ACTION,
                            $shortCode->getMbAction()
                        );

                    } catch (\Exception $e) {
                        MbEvent::callEvents(MocaBonita::getInstance(), MbEvent::EXCEPTION_ACTION, $e);
                        $actionResponse = $e->getMessage();
                    } finally {
                        MbEvent::callEvents(
                            MocaBonita::getInstance(),
                            MbEvent::FINISH_ACTION,
                            $shortCode->getMbAction()
                        );

                        $controllerLog = ob_get_contents();
                        ob_end_clean();
                    }

                    if ($controllerLog != "") {
                        error_log($controllerLog);
                    }

                    if (is_null($actionResponse)) {
                        $actionResponse = $shortCode->getMbAction()->getMbPage()->getController()->getMbView();
                    }

                } catch (\Exception $e) {
                    $actionResponse = $e->getMessage();

                } finally {
                    $mbResponse->setContent($actionResponse);
                }

                $mbResponse->sendContent();

                MbEvent::callEvents(MocaBonita::getInstance(), MbEvent::AFTER_SHORTCODE, $shortCode);
            });
    }
}