<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use App\Entity\Wiki;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

use App\Helpers\ArrayHelper;

use App\Form\Type\WikiType;


class WikiController extends AbstractController
{   
    
    /**
     * @Route("/", name="homepage")
     */
    public function start(): Response
    {   
        
        $repository =$this->getDoctrine()
            ->getRepository(Wiki::class);

        $wikies = $repository->findAll();
        return $this->render('bundles/TwigBundle/Entity/wikies.html.twig', [
            'wikies' => $wikies
        ]);
    }

    /**
     * @Route("/add", name="create_first_wiki")
     */
    public function addFirst(Request $request): Response
    {
        
        // creates a wiki object
        $wiki = new Wiki();
        $wiki->setParent("");
        $wiki->setChildren([]);
        
        $form = $this->createForm(WikiType::class, $wiki);

        $form->handleRequest($request);    
        if ($form->isSubmitted() && $form->isValid()) {

                $em = $this->getDoctrine()->getManager();
                    
                $em->persist($wiki);
                $em->flush();

            return $this->redirectToRoute('view_wiki',['page'=> $wiki->getAddress()]);

        }

        return $this->render('bundles/TwigBundle/Entity/addWiki.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/{page}/add", name="create_wiki" , requirements={"page"="[/a-z0-9_]+"})
     */
    public function add(string $page, Request $request, ArrayHelper $helper): Response
    {
        
        // creates a wiki object
        $wiki = new Wiki();
        $wiki->setParent($page);
        

        $form = $this->createForm(WikiType::class, $wiki);


        $form->handleRequest($request);    
        if ($form->isSubmitted() && $form->isValid()) {

                // TODO transaction

                $em = $this->getDoctrine()->getManager();

                $repository = $this->getDoctrine()->getRepository(Wiki::class);

                $parentWiki = $repository->findOneBy($helper->getParentAndAddress($wiki->getParent()));

                $children = $parentWiki->getChildren();

                $children[] = $wiki->getAddress();

                $parentWiki->setChildren($children);
               
                $em->persist($wiki);
                $em->flush();

                $em->persist($parentWiki);
                $em->flush();
                if (!empty($wiki->getParent())) {
                    return $this->redirectToRoute('view_wiki',['page'=> $wiki->getParent()."/".$wiki->getAddress()]);
                }
                else {
                    return $this->redirectToRoute('view_wiki',['page'=> $wiki->getAddress()]);
                }
                    
                
         }

        return $this->render('bundles/TwigBundle/Entity/addWiki.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{page}/edit", name="edit_wiki", requirements={"page"="[/a-z0-9_]+"})
     */
    public function edit(string $page, Request $request, ArrayHelper $helper): Response
    {   
        $wiki= null;
        $repository =$this->getDoctrine()
            ->getRepository(Wiki::class);

        $wiki = $repository->findOneBy($helper->getParentAndAddress($page));
        if (!$wiki) {
            throw $this->createNotFoundException(); 
        }
        
         $form = $this->createForm(WikiType::class, $wiki);

        $form->handleRequest($request);
                
        if ($form->isSubmitted() && $form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($wiki);
                $em->flush();
                if (!empty($wiki->getParent())) {
                    return $this->redirectToRoute('view_wiki',['page'=> $wiki->getParent()."/".$wiki->getAddress()]);
                }
                else {
                    return $this->redirectToRoute('view_wiki',['page'=> $wiki->getAddress()]);
                }
         }

        return $this->render('bundles/TwigBundle/Entity/addWiki.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{page}/delete", name="delete_wiki", requirements={"page"="[/a-z0-9_]+"})
     */
    public function delete(string $page, Request $request, ArrayHelper $helper): Response
    {   
        $wiki= null;

        $form = $this->createFormBuilder()
            ->add('confirmed', HiddenType::class, ['mapped' => false])
            ->add('delete', SubmitType::class, ['label' => 'Удалить wiki'])

            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

                // TODO transaction

                $em = $this->getDoctrine()
                    ->getManager();
                
                $wiki = $this->getDoctrine()
                    ->getRepository(Wiki::class)
                    ->findOneBy($helper->getParentAndAddress($page));

                if (!$wiki) {
                    throw $this->createNotFoundException();
                }

                if ($wiki->getParent()) {
                    
                    $parentWiki = $this->getDoctrine()
                    ->getRepository(Wiki::class)->findOneBy($helper->getParentAndAddress($wiki->getParent()));

                    $children = $parentWiki->getChildren();

                    $children = array_diff ( $children ,[$wiki->getAddress()] );

                    $parentWiki->setChildren($children);

                    $em->persist($parentWiki);
                    $em->flush();
                }

               


                $em->remove($wiki);
                $em->flush();

                
                
                return $this->redirectToRoute('homepage');
        }

        return $this->render('bundles/TwigBundle/Entity/deleteWiki.html.twig', [
                    'form' => $form->createView(),
                ]);
        }
    /**
     * @Route("{page}", name="view_wiki", requirements={"page"="[/a-z0-9_]+"})
     */
    public function show(string $page, ArrayHelper $helper): Response
    {   
            
        $repository =$this->getDoctrine()
            ->getRepository(Wiki::class);

        $wiki = $repository->findOneBy($helper->getParentAndAddress($page));
       
        if (!$wiki) {
            throw $this->createNotFoundException(); 
        }
     
        return $this->render('bundles/TwigBundle/Entity/wiki.html.twig', ['wiki' => $wiki]);
    }

    
    
    

   
}
