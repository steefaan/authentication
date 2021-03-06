<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Authentication\Authenticator;

use Authentication\Authenticator\StatelessInterface;
use Authentication\Authenticator\UnauthorizedException;
use Authentication\Result;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * HttpBasic Authenticator
 *
 * Provides Basic HTTP authentication support.
 */
class HttpBasicAuthenticator extends AbstractAuthenticator implements StatelessInterface
{

    /**
     * Authenticate a user using HTTP auth. Will use the configured User model and attempt a
     * login using HTTP auth.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request to authenticate with.
     * @param \Psr\Http\Message\ResponseInterface $response The response to add headers to.
     * @return \Authentication\ResultInterface
     */
    public function authenticate(ServerRequestInterface $request, ResponseInterface $response)
    {
        $user = $this->getUser($request);

        if (empty($user)) {
            return new Result(null, Result::FAILURE_IDENTITY_NOT_FOUND);
        }

        return new Result($user, Result::SUCCESS);
    }

    /**
     * Get a user based on information in the request. Used by cookie-less auth for stateless clients.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Request object.
     * @return \Cake\Datasource\EntityInterface|null User entity or null on failure.
     */
    public function getUser(ServerRequestInterface $request)
    {
        $server = $request->getServerParams();
        if (!isset($server['PHP_AUTH_USER']) || !isset($server['PHP_AUTH_PW'])) {
            return null;
        }

        $username = $server['PHP_AUTH_USER'];
        $password = $server['PHP_AUTH_PW'];

        if (!is_string($username) || $username === '' || !is_string($password) || $password === '') {
            return null;
        }

        return $this->identifiers()->identify([
            'username' => $username,
            'password' => $password
        ]);
    }

    /**
     * Create a challenge exception for basic auth challenge.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request A request object.
     * @throws \Authentication\Authenticator\ChallengeException
     * @return void
     */
    public function unauthorizedChallenge(ServerRequestInterface $request)
    {
        throw new UnauthorizedException($this->loginHeaders($request), '');
    }

    /**
     * Generate the login headers
     *
     * @param \Cake\Network\Request $request Request object.
     * @return array Headers for logging in.
     */
    protected function loginHeaders(ServerRequestInterface $request)
    {
        $server = $request->getServerParams();
        $realm = $this->getConfig('realm') ?: $server['SERVER_NAME'];

        return ['WWW-Authenticate' => sprintf('Basic realm="%s"', $realm)];
    }
}
