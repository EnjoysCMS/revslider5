<?php

declare(strict_types=1);

namespace EnjoysCMS\Module\RevSlider5;

use Enjoys\AssetsCollector\Asset;
use EnjoysCMS\Core\Components\Blocks\AbstractBlock;
use EnjoysCMS\Core\Components\Helpers\Assets;
use EnjoysCMS\Core\Entities\Block as Entity;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

use function Enjoys\FileSystem\copyDirectoryWithFilesRecursive;
use function Enjoys\FileSystem\removeDirectoryRecursive;


final class Block extends AbstractBlock
{

    private string $templatePath;

    public function __construct(private Environment $twig, Entity $block)
    {
        parent::__construct($block);
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
                [$url . "/js/jquery.themepunch.tools.min.js", Asset::MINIFY => false, Asset::REPLACE_RELATIVE_URLS => false],
                [$url . "/js/jquery.themepunch.revolution.min.js", Asset::MINIFY => false, Asset::REPLACE_RELATIVE_URLS => false],
                [$url . "/js/jquery.revslider.embed.js", Asset::MINIFY => false, Asset::REPLACE_RELATIVE_URLS => false],
                [$url . "/js/jquery.revslider-".$this->getOption('name').".js", Asset::MINIFY => false, Asset::REPLACE_RELATIVE_URLS => false],
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

    public function preRemove()
    {
        $directory = $_ENV['PUBLIC_DIR'].'/revslider/'.$this->block->getAlias();
        if(is_dir($directory)){
            removeDirectoryRecursive($directory, true);
        }

    }

    /**
     * @throws \Exception
     */
    public function postClone(?Entity $cloned = null)
    {
        $directory_src = $_ENV['PUBLIC_DIR'].'/revslider/'.$this->block->getAlias();
        $directory_dest = $_ENV['PUBLIC_DIR'].'/revslider/'.$cloned->getAlias();
        copyDirectoryWithFilesRecursive($directory_src, $directory_dest);
    }

    public function postEdit(?Entity $oldBlock = null)
    {
        $directory_from = $_ENV['PUBLIC_DIR'].'/revslider/'.$oldBlock->getAlias();
        $directory_to = $_ENV['PUBLIC_DIR'].'/revslider/'.$this->block->getAlias();

        if(file_exists($directory_from)){
            rename($directory_from, $directory_to);
        }

    }


}