<?php

declare(strict_types=1);

namespace EnjoysCMS\Module\RevSlider5;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Enjoys\Forms\Form;
use Enjoys\Forms\Interfaces\RendererInterface;
use EnjoysCMS\Core\Components\Helpers\ACL;
use EnjoysCMS\Core\Components\Helpers\Redirect;
use EnjoysCMS\Core\Entities\Block as Entity;
use EnjoysCMS\Module\Admin\Core\ModelInterface;
use HttpSoft\Message\UploadedFile;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class Import implements ModelInterface
{

    public function __construct(
        private RendererInterface $renderer,
        private ServerRequestInterface $request,
        private EntityManager $entityManager,
        private UrlGeneratorInterface $urlGenerator
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
        }

        $this->renderer->setForm($form);
        return [
            'form' => $this->renderer->output()
        ];
    }

    private function getForm(): Form
    {
        $form = new Form();
        $form->file('slider')->setMaxFileSize(20 * 1024 * 1024);
        $form->submit('submit1');
        return $form;
    }


    /**
     * @throws OptimisticLockException
     * @throws NotFoundExceptionInterface
     * @throws ORMException
     * @throws ContainerExceptionInterface
     * @throws \Exception
     */
    private function doAction(): void
    {
        $tmp_file = $_ENV['TEMP_DIR'] . '/' . uniqid();
        $sliderDir = Uuid::uuid4()->__toString();
        /** @var UploadedFile $file */
        $file = $this->request->getUploadedFiles()['slider'];
        $file->moveTo($tmp_file);

        $uploadFilename = pathinfo($file->getClientFilename(), PATHINFO_FILENAME);
        $extractDirectory = $_ENV['PUBLIC_DIR'] . '/revslider/' . $sliderDir;

        $zip = new \ZipArchive();

        if ($zip->open($tmp_file) === true) {
            $zip->extractTo($extractDirectory);
            $zip->close();
            unlink($tmp_file);

            $sliderName = $this->getSliderName($extractDirectory) ?? $uploadFilename;

            $this->addBlock($sliderName, $sliderDir);
        } else {
            throw new \Exception('Error open file');
        }
        die();
    }


    /**
     * @throws OptimisticLockException
     * @throws NotFoundExceptionInterface
     * @throws ORMException
     * @throws ContainerExceptionInterface
     */
    private function addBlock($sliderName, $sliderDir): void
    {
        $block = new Entity();
        $block->setAlias($sliderDir);
        $block->setName(sprintf('[revslider] %s', $sliderName));
        $block->setRemovable(true);
        $block->setClass(Block::class);
        $block->setOptions(
            [
                'template' => [
                    'value' => '../modules/revslider5/template/block.twig',
                    'name' => 'Путь до template',
                    'description' => 'Обязательно'
                ],
                'name' => [
                    'value' => $sliderName,
                    'name' => 'Название импортированного слайдера',
                    'description' => 'Обязательно'
                ],
            ]
        );
        $this->entityManager->persist($block);
        $this->entityManager->flush();

        ACL::registerAcl(
            $block->getBlockActionAcl(),
            $block->getBlockCommentAcl()
        );

        Redirect::http($this->urlGenerator->generate('admin/blocks'));
    }

    private function getSliderName(string $extractDirectory): ?string
    {
        $searchData = glob($extractDirectory.'/js/jquery.revslider-*.js');
        return null;
    }
}