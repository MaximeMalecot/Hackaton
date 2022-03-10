<?php

namespace App\Controller;

use App\Entity\Brand;
use App\Entity\Product;
use App\Entity\Record;
use App\Entity\Test;
use App\Repository\ProductRepository;
use App\Repository\BrandRepository;
use Doctrine\Persistence\ManagerRegistry;

use App\Form\BrandType;
use App\Form\BrandGenerateType;

use App\Security\Voter\BrandVoter;
use Symfony\Component\Security\Core\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/brand')]
class BrandController extends AbstractController
{
    #[Route('/', name: 'brand_index', methods: ['GET'])]
    public function index(BrandRepository $brandRepository): Response
    {
        return $this->render('brand/index.html.twig', [
            'brands' => $brandRepository->findAll(),
        ]);
    }

    #[IsGranted(BrandVoter::GENERATE)]
    #[Route('/generate', name: 'brand_generate', methods: ['GET', 'POST'])]
    public function generateFromCsv(Request $request,ManagerRegistry $doctrine, ProductRepository $pr, BrandRepository $br, Security $security): Response 
    {
        if($request->isMethod('post')){
            $dataParams = array(
                $request->request->get('product'),
                $request->request->get('session'),
                $request->request->get('zone'),
                $request->request->get('biosens'),
                $request->request->get('result')
            );
            if (($handle = fopen($request->files->get('report'), "r")) !== FALSE) {
                $data = fgetcsv($handle, 1000, ";");
            }
            $brand = $security->getUser()->getBrand();
            $em = $doctrine->getManager();
            //looping
            if(!count(array_unique($dataParams))<count($dataParams) && $brand){
                if (($fp = fopen($request->files->get('report'), "r")) !== FALSE) {
                    while (($row = fgetcsv($fp, 1000, ";")) !== FALSE) {
                        $product = $em->getRepository(Product::class)->findBy(['label' => $request->request->get('product')])[0]??null;
                        if( !$product ){
                            $product = new Product();
                            $product->setLabel($row[$request->request->get('product')]);
                            $product->setBrand($brand);
                            $em->persist($product);
                        }
                        $test = $em->getRepository(Test::class)->findBy([
                            'nbSession' => intval($row[$request->request->get('session')]),
                            'product' => $product
                        ])[0]??null;
                        if( !$test){
                            $test = new Test();
                            $test->setNbSession(intval($row[$request->request->get('session')]));
                            $test->setProduct($product);
                            $em->persist($test);
                        }
                        $record = new Record();
                        $record->setCodeZone(intval($row[$request->request->get('zone')]));
                        $record->setSkinBioSense(intval($row[$request->request->get('biosens')]));
                        $record->setMeasure(floatval($row[$request->request->get('result')]));
                        $record->setTest($test);
                        $em->persist($record);
                    }
                    $em->flush();
                    $this->addFlash('success', 'Votre csv a bien été importé');
                }
            }//error
        }
        return $this->render('brand/generate.html.twig');
    }

    #[Route('/readcsv', name: 'brand_readcsv', methods: ['POST'])]
    public function readCsv(Request $request): Response
    {
        /*$csvFile = file($request->files->get('report'));
        $data = $csvFile[0];
        /*foreach ($csvFile as $line) {
            $data[] = str_getcsv($line);
        }*/
        if (($handle = fopen($request->files->get('report'), "r")) !== FALSE) {
            $data = fgetcsv($handle, 1000, ";");
        }
        
    
        if( !empty($data) && count($data) >=5 ){
            foreach($data as $index => $value){
                $data[$index] = preg_replace('/[^A-Za-z0-9\-\_]/', '', $value);
            }
            return new JsonResponse(["columns" => $data]);
        }
    }

    #[Route('/new', name: 'brand_new', methods: ['GET','POST'])]
    public function new(Request $request): Response
    {
        $brand = new Brand();
        $form = $this->createForm(BrandType::class, $brand);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($brand);
            $entityManager->flush();

            return $this->redirectToRoute('brand_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('brand/new.html.twig', [
            'brand' => $brand,
            'form' => $form,
        ]);
    }

    
/*
    #[Route('/{id}', name: 'brand_show', methods: ['GET'])]
    public function show(Brand $brand): Response
    {
        return $this->render('brand/show.html.twig', [
            'brand' => $brand,
        ]);
    }

    
    #[Route('/{id}/edit', name: 'brand_edit', methods: ['GET','POST'])]
    public function edit(Request $request, Brand $brand): Response
    {
        $form = $this->createForm(BrandType::class, $brand);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('brand_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('brand/edit.html.twig', [
            'brand' => $brand,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'brand_delete', methods: ['POST'])]
    public function delete(Request $request, Brand $brand): Response
    {
        if ($this->isCsrfTokenValid('delete'.$brand->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($brand);
            $entityManager->flush();
        }

        return $this->redirectToRoute('brand_index', [], Response::HTTP_SEE_OTHER);
    }*/
}
