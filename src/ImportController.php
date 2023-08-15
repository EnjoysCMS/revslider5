<?php

declare(strict_types=1);


namespace EnjoysCMS\Module\RevSlider5;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use Doctrine\ORM\EntityManager;
use Enjoys\Forms\Form;
use EnjoysCMS\Core\Block\Entity\Block;
use EnjoysCMS\Core\Block\Options;
use EnjoysCMS\Core\Routing\Annotation\Route;
use EnjoysCMS\Module\Admin\AdminController;
use Exception;
use HttpSoft\Message\UploadedFile;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use ZipArchive;

#[Route(
    path: 'admin/revslider5/import',
    name: '@revslider5_import',
    comment: '[admin] Импорт слайдеров revSlider'
)
]
final class ImportController extends AdminController
{

    public function __construct(
        Container $container,
        private readonly Config $config,
        private readonly EntityManager $em,
    ) {
        parent::__construct($container);
    }

    /**
     * @throws SyntaxError
     * @throws NotFoundException
     * @throws RuntimeError
     * @throws DependencyException
     * @throws LoaderError
     * @throws Exception
     */
    public function __invoke(\EnjoysCMS\Module\Admin\Config $adminConfig): ResponseInterface
    {
        $form = $this->getForm();

        if ($form->isSubmitted()) {
            $block = $this->doAction();
            return $this->redirect->toRoute('@admin_blocks_edit', ['id' => $block->getId()]);
        }

        $renderer = $adminConfig->getRendererForm();
        $renderer->setForm($form);

        $this->breadcrumbs->setLastBreadcrumb('Импорт слайдеров Slider Revolution v5');

        return $this->response(
            $this->twig->render(
                __DIR__ . '/../template/import.twig',
                [
                    'form' => $renderer->output()
                ]
            )
        );
    }

    private function getForm(): Form
    {
        $form = new Form();
        $form->file('slider')->setMaxFileSize(
            $this->config->get('max_file_size', iniSize2bytes(ini_get('upload_max_filesize')))
        );
        $form->submit('import');
        return $form;
    }


    /**
     * @throws Exception
     */
    private function doAction(): Block
    {
        $blockAlias = Uuid::uuid4()->__toString();
        /** @var UploadedFile $file */
        $file = $this->request->getUploadedFiles()['slider'];
        $tmp_file = $_ENV['TEMP_DIR'] . '/' . uniqid();
        $file->moveTo($tmp_file);

        $sliderDirectory = $_ENV['PUBLIC_DIR'] . '/revslider/' . $blockAlias;

        $zip = new ZipArchive();

        if ($zip->open($tmp_file) === true) {
            $zip->extractTo($sliderDirectory);
            $zip->close();
            unlink($tmp_file);

            return $this->addBlock($blockAlias);
        } else {
            throw new Exception('Error open file');
        }
    }

    private function addBlock(string $blockAlias): Block
    {
        $block = new Block();
        $block->setId($blockAlias);
        $block->setName('revSlider');
        $block->setRemovable(true);
        $block->setClassName(\EnjoysCMS\Module\RevSlider5\Block::class);
        $block->setOptions(
            Options::createFromArray(
                [
                    'template' => [
                        'value' => '../modules/revslider5/template/block.twig',
                        'name' => 'Путь до template',
                        'description' => 'Обязательно'
                    ],
                    'sliderData' => [
                        'value' => null,
                        'form' => [
                            'type' => 'file',
                            'data' => [
                                'attributes' => [],
                                'max_file_size' => $this->config->get(
                                    'max_file_size',
                                    iniSize2bytes(ini_get('upload_max_filesize'))
                                )
                            ]
                        ]
                    ]
                ]
            )
        );
        $this->em->persist($block);
        $this->em->flush();

        return $block;
    }

}
