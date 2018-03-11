<?php
/**
 * Created by PhpStorm.
 * User: imanu
 * Date: 10.03.2018
 * Time: 22:35
 */

namespace Jinya\Framework\Security\Api;


use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ApiKeyAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface
{
    /** @var UrlGeneratorInterface */
    private $urlGenerator;
    /** @var TranslatorInterface */
    private $translator;
    /** @var ApiKeyToolInterface */
    private $apiKeyTool;

    /**
     * ApiKeyAuthenticator constructor.
     * @param UrlGeneratorInterface $urlGenerator
     * @param TranslatorInterface $translator
     * @param ApiKeyToolInterface $apiKeyTool
     */
    public function __construct(UrlGeneratorInterface $urlGenerator, TranslatorInterface $translator, ApiKeyToolInterface $apiKeyTool)
    {
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
        $this->apiKeyTool = $apiKeyTool;
    }

    /**
     * @inheritdoc
     */
    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        if (!$userProvider instanceof ApiKeyUserProvider) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The user provider must be an instance of ApiKeyUserProvider (%s was given).',
                    get_class($userProvider)
                )
            );
        }

        $apiKey = $token->getCredentials();

        try {
            if ($this->apiKeyTool->shouldInvalidate($apiKey)) {
                $this->apiKeyTool->invalidate($apiKey);

                throw new CustomUserMessageAuthenticationException($this->translator->trans('api.state.401.expired', ['apiKey' => $apiKey]));
            }
        } catch (Exception $exception) {
            throw new CustomUserMessageAuthenticationException($this->translator->trans('api.state.401.generic', ['apiKey' => $apiKey]));
        }

        $username = $userProvider->getUsernameFromApiKey($apiKey);

        if (empty($username)) {
            throw new CustomUserMessageAuthenticationException(
                sprintf('API Key "%s" does not exist.', $apiKey)
            );
        }

        $user = $userProvider->loadUserByUsername($username);

        return new PreAuthenticatedToken(
            $user,
            $apiKey,
            $providerKey,
            $user->getRoles()
        );
    }

    /**
     * @inheritdoc
     */
    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }

    /**
     * @inheritdoc
     */
    public function createToken(Request $request, $providerKey)
    {
        $key = $request->headers->get('JinyaApiKey', '');

        $login = $this->urlGenerator->generate('api_account_login');

        if ($request->getPathInfo() === $login) {
            /** @noinspection PhpInconsistentReturnPointsInspection */
            return;
        }

        if (empty($key)) {
            throw new BadCredentialsException();
        }

        return new PreAuthenticatedToken('anon.', $key, $providerKey);
    }

    /**
     * @inheritdoc
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($exception instanceof CustomUserMessageAuthenticationException) {
            return new Response($exception->getMessageKey(), Response::HTTP_UNAUTHORIZED);
        } else {
            return new Response($this->translator->trans('api.state.401.generic'), Response::HTTP_UNAUTHORIZED);
        }
    }
}