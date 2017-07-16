<?php
/**
 * Created by PhpStorm.
 * User: cachwir
 * Date: 16/07/17
 * Time: 13:35
 */

namespace AppBundle\Form\Extension\Csrf\CsrfProvider;


use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class MockCsrfProvider implements CsrfTokenManagerInterface
{
    protected $tokens = [];

    public function getToken($tokenId)
    {
        return isset($this->tokens[$tokenId]) ? $this->tokens[$tokenId] : null;
    }

    public function generateToken($tokenId, $token)
    {
        $this->tokens[$tokenId] = $token;
    }

    public function refreshToken($tokenId)
    {
        $this->tokens[$tokenId] = uniqid();
        return $this->tokens[$tokenId];
    }

    public function removeToken($tokenId)
    {
        unset($this->tokens[$tokenId]);
    }

    public function isTokenValid(CsrfToken $token)
    {
        return isset($this->tokens[$token->getId()]) && $this->tokens[$token->getId()] == $token->getValue();
    }

}