<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Conference;
use App\Form\CommentFormType;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConferenceController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em)
    {}

    #[Route('/c-soon', name: 'c_soon')]
    public function cSoon(): Response
    {
        return new Response(<<<EOF
<html>
    <body>
        <img src="/images/coming-soon.gif" />
    </body>
</html>
EOF
        );
    }

    #[Route('/', name: 'homepage')]
    public function index(ConferenceRepository $conferenceRepository): Response
    {
        return $this->render('conference/index.html.twig', []);
    }

    #[Route('/conference/{slug}', name: 'conference')]
    public function show(Request $request, Conference $conference, CommentRepository $commentRepository): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setConference($conference);
            $this->em->persist($comment);
            $this->em->flush();
            $this->redirectToRoute('conference', ['slug' => $conference->getSlug()]);
        }

        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $commentRepository->getCommentPaginator($conference, $offset);
        return $this->render('conference/show.html.twig', [
            'conference'=> $conference,
            'comments'  => $paginator,
            'previous'  => $offset - CommentRepository::PAGINATOR_PER_PAGE,
            'next'      => min(count($paginator), $offset + CommentRepository::PAGINATOR_PER_PAGE),
            'form'      => $form->createView(),
        ]);
    }
}
