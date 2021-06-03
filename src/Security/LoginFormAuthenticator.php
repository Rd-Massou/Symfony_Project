<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

/* Cette class nous permet de gerer les authentification et les redirection de base au sein de notre systeme
de sécurité
*/
class LoginFormAuthenticator extends AbstractFormLoginAuthenticator implements PasswordAuthenticatedInterface
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private $entityManager;
    private $urlGenerator;
    private $csrfTokenManager;
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function supports(Request $request)
    {
        /* On verifier a chaque page visité si elle est celle de login et que le méthode utilisé est POST pour
        pouvoire proceder à getCredentials
        */
        return self::LOGIN_ROUTE === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        /* On récupères les identifiants entré par l'utilisateur ainsi que le jeton csrf pour s'assurer qu'il
        d'agit de données ne prevenant pas d'un attaqueur
        */
        $credentials = [
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        /* On initialise la session pour pouvoir passer des données au travers toute notre application
        */
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['email']
        );

        // On retourn les identifiants pour un usage ultérieurs dans les fonctions getUser et checkCredentials
        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        // On verifie la conformité du jeton csrf
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }
        /* Si le jeton ne pose pas de problème, on cherche l'utilisateur qui est associé à l'adresse mail
        que l'utilisateur a saisie lors du login
        */
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $credentials['email']]);

        if (!$user) {
            // Si on ne trouve pas de utilisateur avec cette adresse mail, nous jettons une exception
            throw new UsernameNotFoundException('Email could not be found.');
        }

        // Si tout se passe bien, on retourne l'utilisateur concerné
        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        /* Puisque on fais un encodage au mot de passe, cette fonction verifie si le mot de passe saisie est le
        que celui qui est hashed dans la base de données.
        */
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    /* Utilisé pour mettre à jour (rehash) le mot de passe de l'utilisateur automatiquement au fil du temps.
     */
    public function getPassword($credentials): ?string
    {
        return $credentials['password'];
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        /* Si l'authentification réussie; càd que les identifiants entrés sont les bons, nous redirigeons
        l'utilisateur à la page de redirection deja vu dans le HomeController
        */
        return new RedirectResponse($this->urlGenerator->generate('app_redirect'));
    }

    protected function getLoginUrl()
    {
        // Dans cette méthode, on retourn la page d'authentification
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
