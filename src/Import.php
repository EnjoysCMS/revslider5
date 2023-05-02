<?php

declare(strict_types=1);

namespace EnjoysCMS\Module\RevSlider5;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Enjoys\Forms\Form;
use Enjoys\Forms\Interfaces\RendererInterface;
use EnjoysCMS\Core\Components\Helpers\ACL;
use EnjoysCMS\Core\Entities\Block as Entity;
use EnjoysCMS\Core\Interfaces\RedirectInterface;
use EnjoysCMS\Module\Admin\Core\ModelInterface;
use Exception;
use HttpSoft\Message\UploadedFile;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use ZipArchive;

final class Import implements ModelInterface
{

    public function __construct(
        private RendererInterface $renderer,
        private ServerRequestInterface $request,
        private EntityManager $entityManager,
        private RedirectInterface $redirect,
        private Config $config
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function getContext(): array
    {
        $form = $this->getForm();

        if ($form->isSubmitted()) {
            $this->doAction();
            $this->redirect->toRoute('admin/blocks', emit: true);
        }

        $this->renderer->setForm($form);
        return [
            'form' => $this->renderer->output()
        ];
    }

    private function getForm(): Form
    {
        $form = new Form();
        $form->file('slider')->setMaxFileSize(
            $this->config->get('max_file_size', iniSize2bytes(ini_get('upload_max_filesize')))
        );
        $form->submit('submit1');
        return $form;
    }


    /**
     * @throws OptimisticLockException
     * @throws NotFoundExceptionInterface
     * @throws ORMException
     * @throws ContainerExceptionInterface
     * @throws Exception
     */
    private function doAction(): void
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

            $this->addBlock($blockAlias);
        } else {
            throw new Exception('Error open file');
        }
    }


    /**
     * @throws OptimisticLockException
     * @throws NotFoundExceptionInterface
     * @throws ORMException
     * @throws ContainerExceptionInterface
     */
    private function addBlock($blockAlias): void
    {
        $block = new Entity();
        $block->setAlias($blockAlias);
        $block->setName('revSlider');
        $block->setRemovable(true);
        $block->setClass(Block::class);
        $block->setOptions(
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
        );
        $this->entityManager->persist($block);
        $this->entityManager->flush();

        ACL::registerAcl(
            $block->getBlockActionAcl(),
            $block->getBlockCommentAcl()
        );
    }
}
