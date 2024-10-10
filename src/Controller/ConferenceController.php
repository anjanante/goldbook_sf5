<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Conference;
use App\Form\CommentFormType;
use App\Repository\CommentRepository;
use App\SpamChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConferenceController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    #[Route('/c-soon', name: 'c_soon')]
    public function cSoon(): Response
    {
        return new Response(
            <<<EOF
<html>
    <body>
        <img src="/images/coming-soon.gif" />
    </body>
</html>
EOF
        );
    }

    #[Route('/', name: 'homepage')]
    public function index(): Response
    {
        return $this->render('conference/index.html.twig', []);
    }

    #[Route('/conference/{slug}', name: 'conference')]
    public function show(Request $request, Conference $conference = null, CommentRepository $commentRepository, SpamChecker $spamChecker, string $photoDir): Response
    {
        if(is_null($conference)){
            return $this->redirectToRoute('homepage');
        }
        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setConference($conference);
            if ($photo = $form['photo']->getData()) {
                $filename = bin2hex(random_bytes(6)) . '.' . $photo->guessExtension();
                try {
                    $photo->move($photoDir, $filename);
                } catch (\Throwable $th) {
                    //throw $th;
                }
                $comment->setPhotoFilename($filename);
            }
            $this->em->persist($comment);

            $context = [
                'user_ip' => $request->getClientIp(),
                'user_agent' => $request->headers->get('user-agent'),
                'referrer' => $request->headers->get('referer'),
                'permalink' => $request->getUri(),
            ];
            if (2 === $spamChecker->getSpamScore($comment, $context)) {
                throw new \RuntimeException('Blatant spam, go away!');
            }

            $this->em->flush();
            return $this->redirectToRoute('conference', ['slug' => $conference->getSlug()]);
        }

        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $commentRepository->getCommentPaginator($conference, $offset);
        return $this->render('conference/show.html.twig', [
            'conference' => $conference,
            'comments'  => $paginator,
            'previous'  => $offset - CommentRepository::PAGINATOR_PER_PAGE,
            'next'      => min(count($paginator), $offset + CommentRepository::PAGINATOR_PER_PAGE),
            'form'      => $form->createView(),
        ]);
    }
}
