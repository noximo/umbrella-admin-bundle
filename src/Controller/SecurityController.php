<?php

namespace Umbrella\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

use function Symfony\Component\Translation\t;

use Umbrella\AdminBundle\Exception\ResetPasswordException;
use Umbrella\AdminBundle\Form\ChangePasswordType;
use Umbrella\AdminBundle\Form\ResetPasswordRequestType;
use Umbrella\AdminBundle\Lib\Controller\AdminController;
use Umbrella\AdminBundle\Service\UserManagerInterface;
use Umbrella\AdminBundle\UmbrellaAdminConfiguration;

class SecurityController extends AdminController
{
    public const LOGIN_ROUTE = 'umbrella_admin_login';
    public const LOGOUT_ROUTE = 'umbrella_admin_logout';

    public function __construct(
        protected readonly UserManagerInterface $userManager,
        protected readonly UmbrellaAdminConfiguration $config
    ) {
    }

    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('@UmbrellaAdmin/security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    public function logout(): never
    {
        throw new \LogicException();
    }

    public function passwordResetRequest(Request $request): Response
    {
        $form = $this->createForm(ResetPasswordRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();

            try {
                $this->userManager->sendResetPasswordEmail($email);
            } catch (ResetPasswordException $e) {
                // proceed as success
            }

            return $this->redirectToRoute('umbrella_admin_security_passwordresetcheckemail');
        }

        return $this->render('@UmbrellaAdmin/security/password_reset_request.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function passwordRequestCheckEmail(): Response
    {
        return $this->render('@UmbrellaAdmin/security/password_reset_check_email.html.twig');
    }

    public function passwordReset(Request $request, string $token): Response
    {
        try {
            $user = $this->userManager->validateResetPasswordTokenAndFetchUser($token);
        } catch (ResetPasswordException $e) {
            return $this->render('@UmbrellaAdmin/security/password_reset_error.html.twig');
        }

        $form = $this->createForm(ChangePasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userManager->updatePassword($user);
            $this->userManager->save($user);

            $this->toastSuccess(t('message.password_resetted', [], 'UmbrellaAdmin'));
            return $this->redirectToRoute(self::LOGIN_ROUTE);
        }

        return $this->render('@UmbrellaAdmin/security/password_reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
