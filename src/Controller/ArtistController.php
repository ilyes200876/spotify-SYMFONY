<?php

namespace App\Controller;

use App\Entity\Artist;
use App\Form\ArtistType;
use App\Repository\ArtistRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Service\FileUploader;

#[Route('/artist')]
class ArtistController extends AbstractController
{
    #[Route('/', name: 'app_artist_index', methods: ['GET'])]
    public function index(ArtistRepository $artistRepository): Response
    {
        return $this->render('artist/index.html.twig', [
            'artists' => $artistRepository->findAll(),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/new', name: 'app_artist_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ArtistRepository $artistRepository, FileUploader $fileUploader): Response
    {
        $artist = new Artist();
        $form = $this->createForm(ArtistType::class, $artist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photo')->getData();
            if ($photoFile) {
                $photoFileName = $fileUploader->upload($photoFile);
                $artist->setImageFilename($photoFileName);
            }
            $artistRepository->save($artist, true);

            return $this->redirectToRoute('app_artist_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('artist/new.html.twig', [
            'artist' => $artist,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_artist_show', methods: ['GET'])]
    public function show(Artist $artist): Response
    {
        return $this->render('artist/show.html.twig', [
            'artist' => $artist,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/edit', name: 'app_artist_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Artist $artist, ArtistRepository $artistRepository, FileUploader $fileUploader): Response
    {
        $form = $this->createForm(ArtistType::class, $artist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photo')->getData();
            if ($photoFile) {
                $photoFileName = $fileUploader->upload($photoFile);
                $artist->setImageFilename($photoFileName);
            }
            $artistRepository->save($artist, true);

            return $this->redirectToRoute('app_artist_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('artist/edit.html.twig', [
            'artist' => $artist,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_artist_delete', methods: ['POST'])]
    public function delete(Request $request, Artist $artist, ArtistRepository $artistRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$artist->getId(), $request->request->get('_token'))) {
            $artistRepository->remove($artist, true);
        }

        return $this->redirectToRoute('app_artist_index', [], Response::HTTP_SEE_OTHER);
    }
}
