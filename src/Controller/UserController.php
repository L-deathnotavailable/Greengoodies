<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
        private readonly TokenStorageInterface $tokenStorage
    ) {}

    /**
     * Page inscription
     */
    #[Route('/registration', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = new User();

        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Inscription réussie ! Vous pouvez maintenant vous connecter.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('user/registration.html.twig', [
            'registrationType' => $form->createView(),
        ]);
    }

    /**
     * Page login
     */
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si déjà connecté -> redirection accueil
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $email = $authenticationUtils->getLastUsername();

        return $this->render('user/login.html.twig', [
            'email' => $email,
            'error' => $error,
        ]);
    }

    /**
     * Route logout
     */
    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method is blank: it is intercepted by the firewall logout.');
    }

    /**
     * Page Mon compte
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/account', name: 'app_account')]
    public function account(): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('user/account.html.twig', [
            'user' => $user,
            'orders' => $user->getOrders(),
        ]);
    }

    /**
     * Supprimer le compte (puis logout via invalidation session)
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/account/delete', name: 'app_account_delete', methods: ['POST'])]
    public function deleteAccount(Request $request): RedirectResponse
    {
        // CSRF
        if (!$this->isCsrfTokenValid('delete_account', (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Action non autorisée.');
            return $this->redirectToRoute('app_account');
        }

        $user = $this->getUser();

        if (!$user instanceof User) {
            $this->addFlash('error', 'Utilisateur non trouvé.');
            return $this->redirectToRoute('app_home');
        }

        try {
            $this->entityManager->remove($user);
            $this->entityManager->flush();

            // Déconnexion explicite
            $this->tokenStorage->setToken(null);

            // Invalidation de la session
            $request->getSession()->invalidate();

            $this->addFlash('success', 'Votre compte a bien été supprimé.');
        } catch (\Throwable $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la suppression du compte.');
            return $this->redirectToRoute('app_account');
        }

        return $this->redirectToRoute('app_home');
    }

    /**
     * Toggle accès API depuis Mon compte
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/account/api_access', name: 'api_access', methods: ['POST'])]
    public function toggleApiAccess(Request $request): RedirectResponse
    {
        // CSRF
        if (!$this->isCsrfTokenValid('toggle_api', (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Action non autorisée.');
            return $this->redirectToRoute('app_account');
        }

        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $user->setApiEnabled(!$user->isApiEnabled());

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->addFlash('success', 'Accès API mis à jour.');

        return $this->redirectToRoute('app_account');
    }
}
