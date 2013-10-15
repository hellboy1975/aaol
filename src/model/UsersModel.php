<?php

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Doctrine\DBAL\Connection;

//namespace AAOL\Model\User;

class AAOLUser implements UserInterface
{
    private $username;
    private $id;
    private $password;
    private $fields;

    private $enabled;
    private $accountNonExpired;
    private $credentialsNonExpired;
    private $accountNonLocked;
    private $roles;

    public function __construct($id, $username, $password, array $roles = array(), $fields, $enabled = true, $userNonExpired = true, $credentialsNonExpired = true, $userNonLocked = true)
    {
        if (empty($username)) {
            throw new \InvalidArgumentException('The username cannot be empty.');
        }

        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
        $this->fields = $fields;

        $this->enabled = $enabled;
        $this->accountNonExpired = $userNonExpired;
        $this->credentialsNonExpired = $credentialsNonExpired;
        $this->accountNonLocked = $userNonLocked;
        $this->roles = $roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * Returns an array containing all the fields from the user record
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonExpired()
    {
        return $this->accountNonExpired;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonLocked()
    {
        return $this->accountNonLocked;
    }

    /**
     * {@inheritdoc}
     */
    public function isCredentialsNonExpired()
    {
        return $this->credentialsNonExpired;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }
}


class UserProvider implements UserProviderInterface
{
    private $db;

    public $lastSQL;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function loadUserByUsername($username)
    {
        $stmt = $this->db->executeQuery('SELECT * FROM user WHERE username = ?', array(strtolower($username)));

        if (!$user = $stmt->fetch()) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }

        return new AAOLUser($user['id'], $user['username'], $user['password'], explode(',', $user['roles']), $user,  true, true, true, true);
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof AAOLUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        // not sure what this should be set to, but it appears to work for now!
        return $class === 'Symfony\Component\Security\Core\User\User';
    }

    private function _usersSelect()
    {
        return "SELECT id, username, roles, first_name, last_name, email, status, bio
                    FROM user";
    }

    public function fetchUser($username) 
    {
        $select = $this->_usersSelect();      
        
        $sql = "$select 
                WHERE username = ?";

        return $this->db->fetchAssoc($sql, array((string) $username));     
    }

    public function fetchUsers($role = 'ALL', $status = 'ALL', $order = USER_ORDER_USERNAME_ASC, $limit = 100, $offset = 0) 
    {
        if ($order = USER_ORDER_USERNAME_ASC) $orderBy = "username ASC";
        else $orderBy = "username DESC";

        $select = $this->_usersSelect();

        if (( $role != 'ALL' ) && ( $status != 'ALL' ))
        {
            $sql = "$select 
                    WHERE roles = ? AND status = ?
                    ORDER BY $orderBy
                    LIMIT {$offset}, {$limit}";

            $users = $this->db->fetchAll($sql, array((string) $role, (string) $status));   
        }

        else if ( $role != 'ALL' )
        {
            $sql = "$select 
                    WHERE roles = ? 
                    ORDER BY $orderBy
                    LIMIT {$offset}, {$limit}";

            $users = $this->db->fetchAll($sql, array((string) $role));   
        }

        else if ( $status != 'ALL' )
        {
            $sql = "$select 
                    WHERE status = ? 
                    ORDER BY $orderBy
                    LIMIT {$offset}, {$limit}";

            $users = $this->db->fetchAll($sql, array((string) $status));   
        }

        else 
        {
            $sql = "$select 
                    ORDER BY $orderBy
                    LIMIT {$offset}, {$limit}";

            $users = $this->db->fetchAll($sql);   
        }

        $this->lastSQL = $sql;
        
        return $users; 
    }
}