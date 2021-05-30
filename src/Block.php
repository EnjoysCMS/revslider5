<?php

declare(strict_types=1);


namespace EnjoysCMS\Module\RevSlider5;


use EnjoysCMS\Core\Components\Blocks\AbstractBlock;
use EnjoysCMS\Core\Components\Helpers\Assets;
use EnjoysCMS\Core\Entities\Blocks as Entity;
use Psr\Container\ContainerInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

final class Block extends AbstractBlock
{

    private Environment $twig;
    private string $templatePath;

    public function __construct(ContainerInterface $container, Entity $block)
    {
        parent::__construct($container, $block);
        $this->twig = $this->container->get(Environment::class);
        $this->templatePath = $this->getOption('template');
    }

    public static function getBlockDefinitionFile(): string
    {
        return '';
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function view(): string
    {

        $url = '//'.$_SERVER['HTTP_HOST'].'/revslider/'.$this->block->getAlias();
        Assets::js(
            [
                $url . "/js/jquery.themepunch.tools.min.js",
                $url . "/js/jquery.themepunch.revolution.min.js",
                $url . "/js/jquery.revslider.embed.js",
                $url . "/js/jquery.revslider-".$this->getOption('name').".js"
            ]
        );

        return $this->twig->render(
            $this->templatePath,
            [
                'blockOptions' => $this->getOptions(),
                'sliderUrl' => '/revslider/'.$this->block->getAlias()

            ]
        );
    }
}