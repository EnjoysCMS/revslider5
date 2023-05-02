<?php

declare(strict_types=1);

namespace EnjoysCMS\Module\RevSlider5;

use Enjoys\AssetsCollector\AssetOption;
use Enjoys\AssetsCollector\Assets;
use EnjoysCMS\Core\Components\Blocks\AbstractBlock;
use EnjoysCMS\Core\Entities\Block as Entity;
use Exception;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use ZipArchive;

use function Enjoys\FileSystem\copyDirectoryWithFilesRecursive;
use function Enjoys\FileSystem\removeDirectoryRecursive;


final class Block extends AbstractBlock
{

    private string $revsliderDirectory;
    private string $templatePath;

    public function __construct(
        private Environment $twig,
        private ServerRequestInterface $request,
        private Assets $assets,
        Entity $block
    ) {
        parent::__construct($block);
        $this->templatePath = $this->getOption('template');
        $this->revsliderDirectory = $_ENV['REVSLIDER_DIR'] ?? $_ENV['PUBLIC_DIR'] . '/revslider';
    }

    public static function getBlockDefinitionFile(): string
    {
        return '';
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function view(): string
    {
        $scriptsPath = glob($this->revsliderDirectory . '/' . $this->block->getAlias() . '/js/*.js');
        $sliderAlias = null;

        foreach ($scriptsPath as $script) {
            if (str_contains($script, 'jquery.revslider-')) {
                $sliderAlias = str_replace('jquery.revslider-', '', pathinfo($script, PATHINFO_FILENAME));
            }
            $this->assets->add('js', [
                    [
                        str_replace($this->revsliderDirectory, '//' . $_SERVER['HTTP_HOST'] . '/revslider', $script),
                        AssetOption::MINIFY => false,
                        AssetOption::REPLACE_RELATIVE_URLS => false
                    ]
                ]
            );
        }

        return $this->twig->render(
            $this->templatePath,
            [
                'slider' => [
                    'alias' => $sliderAlias,
                    'url' => '/revslider/' . $this->block->getAlias()
                ]

            ]
        );
    }

    public function preRemove(): void
    {
        $directory = $this->revsliderDirectory . '/' . $this->block->getAlias();
        if (is_dir($directory)) {
            removeDirectoryRecursive($directory, true);
        }
    }

    /**
     * @throws Exception
     */
    public function postClone(?Entity $cloned = null): void
    {
        $directory_src = $this->revsliderDirectory . '/' . $this->block->getAlias();
        $directory_dest = $this->revsliderDirectory . '/' . $cloned->getAlias();
        copyDirectoryWithFilesRecursive($directory_src, $directory_dest);
    }

    /**
     * @throws Exception
     */
    public function postEdit(?Entity $oldBlock = null): void
    {
        $directory_from = $this->revsliderDirectory . '/' . $oldBlock->getAlias();
        $directory_to = $this->revsliderDirectory . '/' . $this->block->getAlias();

        if (file_exists($directory_from)) {
            rename($directory_from, $directory_to);
        }

        /** @var UploadedFileInterface $file */
        $file = $this->request->getUploadedFiles()['options']['sliderData'];
        if ($file->getError() === 0) {
            $tmp_file = $_ENV['TEMP_DIR'] . '/' . uniqid();
            $file->moveTo($tmp_file);
            $zip = new ZipArchive();
            removeDirectoryRecursive($directory_to);

            if ($zip->open($tmp_file) === true) {
                $zip->extractTo($directory_to);
                $zip->close();
                unlink($tmp_file);
            } else {
                throw new Exception('Error open file');
            }
        }
    }
}
