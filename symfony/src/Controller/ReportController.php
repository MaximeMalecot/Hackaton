<?php

namespace App\Controller;


use App\Entity\Brand;
use App\Entity\Product;
use App\Repository\ProductRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Core\Security;
use Doctrine\Persistence\ManagerRegistry;

class ReportController extends AbstractController
{
    #[Route('/report', name: 'app_report')]
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

    #[Route('/report/brand/{id}', name: 'report_brand')]
    public function showBrandReports(Brand $brand, ProductRepository $pr): Response
    {
        return $this->render('report/products.html.twig', [ 
            'products' => $pr->findBy([
                'brand' => $brand
            ])
        ]);
    }

    #[Route('/report/product/{id}', name: 'report_product')]
    public function showProductReport(Product $product): Response
    {
        dd('notyet');
        return $this->render('report/products.html.twig', [ 
            'products' => $pr->findBy([
                'brand' => $brand
            ])
        ]);
    }



}
