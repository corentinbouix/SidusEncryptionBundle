<?php

namespace Sidus\EncryptionBundle\Authentication;

use Sidus\EncryptionBundle\Entity\UserEncryptionProviderInterface;
use Sidus\EncryptionBundle\Security\EncryptionManager;
use Symfony\Component\Security\Core\Authentication\Provider\DaoAuthenticationProvider;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * The Authentication provider will be used at connection time to decrypt the cipher key in the user and store it in
 * session through the encryption manager.
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
class AuthenticationProvider extends DaoAuthenticationProvider
{
    /** @var EncryptionManager */
    protected $encryptionManager;

    /**
     * @param EncryptionManager $encryptionManager
     *
     * @return AuthenticationProvider
     */
    public function setEncryptionManager(EncryptionManager $encryptionManager)
    {
        $this->encryptionManager = $encryptionManager;

        return $this;
    }

    /**
     * Retrieve user with password token and use it to decrypt the cipher key in the user
     * The encryption manager will store it in the session for the following requests
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     * @throws \Sidus\EncryptionBundle\Exception\EmptyCipherKeyException
     * @throws \Sidus\EncryptionBundle\Exception\EmptyOwnershipIdException
     */
    protected function retrieveUser($username, UsernamePasswordToken $token)
    {
        $user = parent::retrieveUser($username, $token);
        if ($user instanceof UserEncryptionProviderInterface && null !== $token->getCredentials()) {
            $this->encryptionManager->decryptCipherKey($user, $token->getCredentials());
        }

        return $user;
    }
}
