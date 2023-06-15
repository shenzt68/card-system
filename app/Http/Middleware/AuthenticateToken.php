<?php
namespace App\Http\Middleware; use Closure; use Illuminate\Auth\AuthenticationException; use Illuminate\Support\Facades\Auth; use Illuminate\Support\Facades\Log; use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException; use Tymon\JWTAuth\Exceptions\JWTException; use Tymon\JWTAuth\Http\Middleware\BaseMiddleware; use Tymon\JWTAuth\Exceptions\TokenExpiredException; class AuthenticateToken extends BaseMiddleware { private function checkTokenTime() { $spbfa519 = $this->auth->user(); $sp6d6c78 = $this->auth->manager()->getPayloadFactory()->buildClaimsCollection()->get('iat')->getValue(); if ($sp6d6c78 < $spbfa519->remember_token) { return false; } return true; } public function handle($sp62e4cd, Closure $sp530731) { if (is_string($sp62e4cd->query('token'))) { $sp62e4cd->headers->set('Authorization', $sp62e4cd->query('token')); } $this->checkForToken($sp62e4cd); try { if ($this->auth->parseToken()->authenticate()) { if ($this->checkTokenTime()) { return $sp530731($sp62e4cd); } else { throw new UnauthorizedHttpException('jwt-auth', 'Token invalid'); } } else { throw new UnauthorizedHttpException('jwt-auth', 'User not found'); } } catch (TokenExpiredException $sp9cf6e9) { try { $sp97507d = $this->auth->refresh(); Auth::onceUsingId($this->auth->manager()->getPayloadFactory()->buildClaimsCollection()->get('sub')->getValue()); if ($this->checkTokenTime()) { return $this->setAuthenticationHeader($sp530731($sp62e4cd), $sp97507d); } else { throw new UnauthorizedHttpException('jwt-auth', 'Token invalid'); } } catch (JWTException $sp9cf6e9) { throw new UnauthorizedHttpException('jwt-auth', $sp9cf6e9->getMessage()); } } catch (JWTException $sp9cf6e9) { throw new UnauthorizedHttpException('jwt-auth', $sp9cf6e9->getMessage()); } } }