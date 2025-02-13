<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Routing\RouterInterface;

class CustomAuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function start(Request $request, AuthenticationException $authException = null): JsonResponse|RedirectResponse
    {
        // Если это AJAX-запрос или API, возвращаем JSON
        if ($request->isXmlHttpRequest() || str_starts_with($request->getPathInfo(), '/api')) {
            return new JsonResponse([
                'error' => 'Unauthorized access',
                'message' => $authException?->getMessage(),
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // В противном случае перенаправляем на страницу логина
        return new RedirectResponse($this->router->generate('login'));
    }
}
