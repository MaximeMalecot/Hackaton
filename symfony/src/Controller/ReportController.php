<?php

namespace App\Controller;


use App\Entity\Brand;
use App\Entity\Product;
use App\Entity\Record;
use App\Entity\Test;
use App\Repository\ProductRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Core\Security;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[Route('/report')]
class ReportController extends AbstractController
{
    const SKIN_BIOSENSE = [
        1 => 'Protect',
        2 => 'Hydrate',
        3 => 'Barrier'
    ];

    #[Route('/', name: 'app_report')]
    public function index(ManagerRegistry $doctrine, Security $security): Response
    {
        $em=$doctrine->getManager();
        $user = $security->getUser();
        if(in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('report/index.html.twig', [
                'brands' => $em->getRepository(Brand::class)->findAll()
            ]);

        } else {
            return $this->render('report/products.html.twig', [ 
                'products' => $em->getRepository(Product::class)->findAll()
            ]);
        }
    }

    #[Route('/brand/{id}', name: 'report_brand')]
    public function showBrandReports(Brand $brand, ProductRepository $pr): Response
    {
        return $this->render('report/products.html.twig', [ 
            'products' => $pr->findBy([
                'brand' => $brand
            ])
        ]);
    }

    #[Route('/product/{id}', name: 'report_product')]
    public function showProductReport(Request $request, Product $product, ManagerRegistry $doctrine, ChartBuilderInterface $chartBuilder): Response
    {
        $em = $doctrine->getManager();
        $tests = $em->getRepository(Test::class)->findBy(['product' => $product], ['nbSession' => 'ASC']);
        if(!$request->query->get('session')){
            $session = $tests[0]->getNbSession();
        } else {
            $session = $request->query->get('session');
        }
        $availableSessions = [];
        foreach($tests as $test){
            if($test->getNbSession() == $session){
                $records = $test->getRecords()->getValues();
            }
            $availableSessions[] = $test->getNbSession();
        }
        $zonedRecords = null;
        foreach($records as $record){
            $zonedRecords[$record->getCodeZone()][$record->getSkinBioSense()][] = $record;

            //$zonedRecords[$record->getCodeZone()][] = $record;
        }

        $chartsByZone = [];
        foreach ($zonedRecords as $zonedRecord) {
            $chartsBySkinBioSense = [];
            foreach ($zonedRecord as $skinBioSenseRecords) {
                $chart = $chartBuilder->createChart(Chart::TYPE_BAR);

                $values = [];
                foreach ($skinBioSenseRecords as $record) {
                    $index = strval($record->getMeasure());
                    if (!key_exists($index, $values)) {
                        $values[$index] = 0;
                    }
                    $values[$index] ++;
                }
                ksort($values);

                $labels = array_map(
                    function($index) { return floatval($index); },
                    array_keys($values)
                );
                dump($labels);
                $data = [];
                foreach ($values as $value) {
                    array_push($data, $value);
                }
                dump($data);

                $chart->setData([
                    'labels' => $labels,
                    'datasets' => [
                        [
                            'label' => self::SKIN_BIOSENSE[$skinBioSenseRecords[0]->getSkinBioSense()],
                            'backgroundColor' => 'rgb(255, 99, 132)',
                            'borderColor' => 'rgb(255, 99, 132)',
                            'data' => $data,
                        ],
                    ],
                ]);

                $chartsBySkinBioSense[] = $chart;
            }

            $chartsByZone[] = $chartsBySkinBioSense;
        }

        return $this->render('report/charts.html.twig', [
            'product_id' => $product->getId(),
            'sessions'=> $availableSessions,
            'zonedRecords' => $zonedRecords,
            'charts' => $chartsByZone
        ]);
    }
}
