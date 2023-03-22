<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\RegistrationFormType;
use App\Repository\UsersRepository;
use App\Security\UsersAuthenticator;
use App\Service\JWTService;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/inscription', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, UsersAuthenticator $authenticator, EntityManagerInterface $entityManager, SendMailService $mail, JWTService $jwt): Response
    {
        $user = new Users();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            // On genet le JWT de l'utilisateur
            // Je cree le Header
            $header = [
                'typ' => 'JWT',
                'alg' => 'HS256'
            ];

            // Je cree le Payload
            $payload = [
                'user_id' => $user->getId()
            ];

            //Je genere le token
            $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

            // On envoei un mail

            $mail->send(
                'no-replay@test.fr',
                $user->getEmail(),
                'Actuvation de votre compte sur le site E-commerce',
                'register',
                compact('user', 'token')
            );

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
    #[Route('verif/{token}', name: 'verify_user')]
    public function verifyUser($token, JWTService $jwt, UsersRepository $usersRepository, EntityManagerInterface $em): Response
    {
        // Je verifie si le token est valid, n'a pas expiré et n'a pas ete modifier
        if ($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('app.jwtsecret'))){

            //Je recupere le payload
            $payload = $jwt->getPayload($token);

            // Je recupere le user du token
            $user = $usersRepository->find($payload['user_id']);

            // Je verifier que l'utilisateur existe et n'a pas activé son compte

            if ($user && !$user->getIsVerified()){
                $user->setIsVerified(true);
                $em->flush($user);
                $this->addFlash('success', 'Votre compte est désormais Activé, Bienvenue :)');
                return $this->redirectToRoute('profile_index');
            }
        }
        // et si le probleme se pose dans le token
        $this->addFlash('danger', 'Attention! le token est invalid ou a éxpiré');
        return $this->redirectToRoute('app_login');
    }
    #[Route('/renvoiverif', name: 'resend_verif')]
    public function resendVerif(JWTService $jwt, SendMailService $mail, UsersRepository $usersRepository): Response
    {
        $user = $this->getUser();

        if (!$user){
            $this->addFlash('danger', 'Vous devez étre connecté pour accéder à cette page ');
            return $this->redirectToRoute('app_login');
        }
        if ($user->getIsVerified()){
            $this->addFlash('warning', 'cet utilisateur est déjà activé');
            return $this->redirectToRoute('profile_index');
        }
        // On genet le JWT de l'utilisateur
        // Je cree le Header
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];

        // Je cree le Payload
        $payload = [
            'user_id' => $user->getId()
        ];

        //Je genere le token
        $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

        // On envoei un mail

        $mail->send(
            'no-replay@test.fr',
            $user->getEmail(),
            'Actuvation de votre compte sur le site E-commerce',
            'register',
            compact('user', 'token')
        );
        $this->addFlash('success', 'Email de vérification envoyé  ');
        return $this->redirectToRoute('profile_index');
    }

}
