<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\CheckEmailFormType;
use App\Form\UserNameFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\UX\Turbo\TurboBundle;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_security_login')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/register', name: 'app_security_register')]
    public function register(Request $request, UserRepository $userRepository): Response
    {
        $form = $this->createForm(CheckEmailFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();

            $existingUser = $userRepository->findOneBy(['email' => $email]);

            if ($existingUser) {
                $this->addFlash('danger', "L'adresse e-mail est déjà utilisée.");

                if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                    $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                    return $this->render('_partials/_flashes.stream.html.twig');
                }
            } else {
                $request->getSession()->set('registration_email', $email);

                return $this->redirectToRoute('app_register_name');
            }
        }

        return $this->render('security/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/register/complete', name: 'app_register_name')]
    public function registerName(Request $request, UserRepository $userRepository, EntityManagerInterface $em): Response
    {
        $email = $request->getSession()->get('registration_email');
        if (!$email) {
            $this->addFlash('danger', 'Adresse e-mail requise.');
            return $this->redirectToRoute('app_security_register');
        }

        $user = (new User())->setEmail($email);
        $form = $this->createForm(UserNameFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $role = $form->get('attachedRoles')->getData();

            $user->setUsername($email);
            $user->setEmail($email);
            $user->addattachedRole($role);

            $request->getSession()->remove('registration_email');
            $request->getSession()->set('user', $user);

            return $this->redirectToRoute('app_home');
        }

        return $this->render('security/register_complete.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function registerPassword(): Response
    {
        // This method is intentionally left blank.
        // Password setting logic would go here.
        return $this->render('security/register_password.html.twig');
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
