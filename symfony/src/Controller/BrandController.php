<?php

namespace App\Controller;

use App\Entity\Brand;
use App\Entity\User;
use App\Entity\Product;
use App\Entity\Record;
use App\Entity\Test;
use App\Form\AccessBrandType;
use App\Form\BrandType;
use App\Repository\ProductRepository;
use App\Repository\BrandRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Form\BrandGenerateType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Routing\Annotation\Route;
use App\Security\Voter\BrandVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/brand')]
class BrandController extends AbstractController
{
    private $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    #[Route('/', name: 'brand_index', methods: ['GET'])]
    public function index(BrandRepository $brandRepository): Response
    {
        return $this->render('brand/index.html.twig', [
            'brands' => $brandRepository->findAll(),
        ]);
    }

    #[IsGranted(BrandVoter::GENERATE)]
    #[Route('/generate', name: 'brand_generate', methods: ['GET', 'POST'])]
    public function generateFromCsv(Request $request,ManagerRegistry $doctrine, Security $security): Response 
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
            //looping
            if(!(count(array_unique($dataParams))<count($dataParams))){
                $em = $doctrine->getManager();
                if (($fp = fopen($request->files->get('report'), "r")) !== FALSE) {
                    $row = fgetcsv($fp, 1000, ";");
                    while (($row = fgetcsv($fp, 1000, ";")) !== FALSE) {
                        $product = $em->getRepository(Product::class)->findOneBy(['label' => $row[$request->request->get('product')]]);
                        if( !$product ){
                            $product = new Product();
                            $product->setLabel($row[$request->request->get('product')]);
                            $product->setBrand($security->getUser()->getBrand());
                            $em->persist($product);
                            $em->flush();
                        }
                        $test = $em->getRepository(Test::class)->findOneBy([
                            'nbSession' => intval($row[$request->request->get('session')]),
                            'product' => $product
                        ]);
                        if( !$test){
                            $test = new Test();
                            $test->setNbSession(intval($row[$request->request->get('session')]));
                            $test->setProduct($product);
                            $em->persist($test);
                            $em->flush();
                        }
                        $record = new Record();
                        $record->setCodeZone(intval($row[$request->request->get('zone')]));
                        $record->setSkinBioSense(intval($row[$request->request->get('biosens')]));
                        $record->setMeasure(floatval($row[$request->request->get('result')]));
                        $em->persist($record);
                        $test->addRecord($record);
                    }
                    $em->flush();
                    $this->addFlash('success', 'Votre csv a bien été importé');
                }
            }else {
                $this->addFlash('error', 'Vous ne pouvez pas utiliser une même colonne pour plusieurs données.');
            }
        }
        return $this->render('brand/generate.html.twig');
    }

    #[Route('/readcsv', name: 'brand_readcsv', methods: ['POST'])]
    public function readCsv(Request $request): Response
    {
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

    #[Route('/{id}', name: 'brand_show', methods: ['GET'])]
    public function show(Brand $brand): Response
    {
        return $this->render('brand/show.html.twig', [
            'brand' => $brand,
        ]);
    }

    
    #[Route('/{id}/edit', name: 'brand_edit', methods: ['GET','POST'])]
    #[Route('/access/edit/{id}', name: 'brand_access_edit')]
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
    }

    #[Route('/access/{id}', name: 'brand_access')]
    public function access(Request $request, Brand $brand, MailerInterface $mailer): Response
    {
        $user = new User();

        $form = $this->createForm(AccessBrandType::class, $user);
        $form->handleRequest($request);

        if (!$user->isVerified() && $form->isSubmitted() && $form->isValid()) {
            $user->setBrand($brand);
            $user->setRoles(['ROLE_BRAND']);
            $user->setPassword('isCreation');

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $email = (new TemplatedEmail())
                ->from('wiredbeauty@support.fr')
                ->to($user->getEmail())
                ->subject('Wired Beauty - Create your access account')
                ->text('Your account access !')
                ->htmlTemplate('email/access_account.html.twig')
                ->context([
                    'user' => $user,
                ]);

            $mailer->send($email);

            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('brand/access.html.twig', [
            'brand' => $brand,
            'form' => $form
        ]);
    }
}
