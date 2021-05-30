<?php

declare(strict_types=1);


namespace EnjoysCMS\Module\RevSlider5;


use App\Module\Admin\BaseController;
use EnjoysCMS\Module\Catalog\Models\Admin\Category\Index;
use Psr\Container\ContainerInterface;
use Symfony\Component\Routing\Annotation\Route;

final class Controller extends BaseController
{
    #[Route(
        path: 'admin/revslider5/import',
        name: 'revslider5/import',
        options: [
            'aclComment' => '[admin] Импорт слайдеров revSlider'
        ]
    )
    ]
    public function upload(ContainerInterface $container): string
    {
        return $this->view(
            __DIR__ . '/../template/import.twig',
            $this->getContext($container->get(Import::class))
        );
    }

}