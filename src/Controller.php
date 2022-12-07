<?php

declare(strict_types=1);


namespace EnjoysCMS\Module\RevSlider5;

use EnjoysCMS\Core\BaseController;
use EnjoysCMS\Module\Admin\AdminBaseController;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;


final class Controller extends AdminBaseController
{

    #[Route(
        path: 'admin/revslider5/import',
        name: 'revslider5/import',
        options: [
            'aclComment' => '[admin] Импорт слайдеров revSlider'
        ]
    )
    ]
    public function upload(Import $import): ResponseInterface
    {
        return $this->responseText($this->view(
            __DIR__ . '/../template/import.twig',
            $this->getContext($import)
        ));
    }

}