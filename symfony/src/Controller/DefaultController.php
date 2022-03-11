<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Record;
use App\Entity\Test;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'default')]
    #[Route('/', name: 'app_home')]
    public function index(ChartBuilderInterface $chartBuilder): Response
    {
        $entityManger = $this->getDoctrine()->getManager();

        $availableSessions = [];
        $test = $entityManger->getRepository(Test::class)->findOneBy(
            [],
            ['id' => 'DESC']
        );
        if ($test) {
            for ($i = 0; $i < $test->getNbSession(); $i ++) {
                $availableSessions[] = ($i + 1);
            }
        }

        return $this->render('default/index.html.twig', [
            'controller_name' => 'index',
            'availableSessions' => $availableSessions,
            'product' => $test && $test->getProduct() ? $test->getProduct() : null,
            'simpleData' => [
                'products' => [
                    'label' => 'Nb. products',
                    'value' => count($entityManger->getRepository(Product::class)->findAll())
                ],
                'tests' => [
                    'label' => 'Nb. tests',
                    'value' => count($entityManger->getRepository(Test::class)->findAll())
                ],
                'records' => [
                    'label' => 'Nb. records',
                    'value' => count($entityManger->getRepository(Record::class)->findAll())
                ],
            ]
        ]);
    }
}
