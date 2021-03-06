<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Authentication\Authenticator;

use Authentication\Identifier\IdentifierCollection;
use Authentication\PasswordHasher\DefaultPasswordHasher;
use Authentication\PasswordHasher\PasswordHasherTrait;
use Cake\Core\InstanceConfigTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractAuthenticator implements AuthenticateInterface
{

    use InstanceConfigTrait;
    use PasswordHasherTrait;

    /**
     * Default config for this object.
     * - `fields` The fields to use to identify a user by.
     * - `userModel` The alias for users table, defaults to Users.
     * - `finder` The finder method to use to fetch user record. Defaults to 'all'.
     *   You can set finder name as string or an array where key is finder name and value
     *   is an array passed to `Table::find()` options.
     *   E.g. ['finderName' => ['some_finder_option' => 'some_value']]
     * - `passwordHasher` Password hasher class. Can be a string specifying class name
     *    or an array containing `className` key, any other keys will be passed as
     *    config to the class. Defaults to 'Default'.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'fields' => [
            'username' => 'username',
            'password' => 'password'
        ],
        'userModel' => 'Users',
        'finder' => 'all',
        'passwordHasher' => DefaultPasswordHasher::class
    ];

    /**
     * Identifier collection
     *
     * @var \Authentication\Identifier\IdentifierCollection
     */
    protected $_identifiers;

    /**
     * Constructor
     *
     * @param \Authentication\Identifier\IdentifierCollection $identifiers Array of config to use.
     * @param array $config Configuration settings.
     */
    public function __construct(IdentifierCollection $identifiers, array $config = [])
    {
        $this->setConfig($config);
        $this->_identifiers = $identifiers;
    }

    /**
     * Gets the identifier collection
     *
     * @return \Authentication\Identifier\IdentifierCollection
     */
    public function identifiers()
    {
        return $this->_identifiers;
    }

    /**
     * Authenticate a user based on the request information.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Request to get authentication information from.
     * @param \Psr\Http\Message\ResponseInterface $response A response object that can have headers added.
     * @return mixed Either false on failure, or an array of user data on success.
     */
    abstract public function authenticate(ServerRequestInterface $request, ResponseInterface $response);
}
