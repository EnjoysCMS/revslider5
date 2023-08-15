<?php

declare(strict_types=1);

namespace EnjoysCMS\Module\RevSlider5;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Enjoys\Forms\Interfaces\RendererInterface;
use EnjoysCMS\Core\Components\Helpers\ACL;
use EnjoysCMS\Core\Entities\Block as Entity;
use EnjoysCMS\Core\Interfaces\RedirectInterface;
use EnjoysCMS\Module\Admin\Core\ModelInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ServerRequestInterface;

final class Import
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
