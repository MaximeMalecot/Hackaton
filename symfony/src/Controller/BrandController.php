<?php

namespace App\Controller;

use App\Entity\Brand;
use App\Entity\User;
use App\Form\AccessBrandType;
use App\Form\BrandType;
use App\Form\BrandGenerateType;
use App\Repository\BrandRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Routing\Annotation\Route;
use App\Security\Voter\BrandVoter;
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
    public function generateFromCsv(Request $request): Response 
    {
        
        return $this->render('brand/generate.html.twig');
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
