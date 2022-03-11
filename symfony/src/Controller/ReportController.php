<?php

namespace App\Controller;


use App\Entity\Brand;
use App\Entity\Product;
use App\Entity\Test;
use App\Repository\ProductRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Core\Security;
use Doctrine\Persistence\ManagerRegistry;

#[Route('/report')]
class ReportController extends AbstractController
{
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
    public function showProductReport(Request $request, Product $product, ManagerRegistry $doctrine): Response
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
        dd($zonedRecords);
        /*
        return $this->render('report/charts.html.twig', [
            'product_id' => $product->getId(),
            'sessions'=> $availableSessions,
            'zonedRecords' => $zonedRecords
        ]);*/
    }
}
