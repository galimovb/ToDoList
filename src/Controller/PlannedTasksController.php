<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlannedTasksController extends AbstractController
{
    #[Route('/planned', name: 'planned')]
    public function index(LoggerInterface $logger): Response
    {
        $logger->info('Пользователь перешел на страницу запланированных задач', [
            'user' => $this->getUser() ? $this->getUser()->getUserIdentifier() : 'guest',
            'time' => date('Y-m-d H:i:s')
        ]);

        return $this->render('planned-tasks-page/index.html.twig', [
            'vue_component' => 'planned-tasks-page',
        ]);
    }
}