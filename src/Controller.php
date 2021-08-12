<?php

declare(strict_types=1);


namespace EnjoysCMS\Module\RevSlider5;

use App\Module\Admin\BaseController;
use Psr\Container\ContainerInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class Controller
 * @package EnjoysCMS\Module\RevSlider5
 */
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
    public function upload(): string
    {
        return $this->view(
            __DIR__ . '/../template/import.twig',
            $this->getContext($this->getContainer()->get(Import::class))
        );
    }

}