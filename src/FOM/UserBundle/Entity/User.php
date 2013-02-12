<?php

/**
 * TODO: Validation
 * TODO: Basic user data
 * TODO: User profiles
 */

namespace FOM\UserBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * User entity.
 *
 * This needs enhancement, email should probably required. And we need a way
 * to implements user profiles which can vary from installation to
 * installation.
 *
 * @author Christian Wygoda
 * @author apour
 * @author Paul Schmidt
 *
 * @ORM\Entity
 * @UniqueEntity("username")
 * @UniqueEntity("email")
 * @ORM\Table(name="fom_user")
 */
class User implements AdvancedUserInterface {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=false, length=512, unique=true)
     * @Assert\NotBlank()
     * @Assert\MinLength(3)
     */
    protected $username;

    /**
     * @ORM\Column(type="string", nullable=false, length=512, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    protected $email;

    /**
     * @ORM\Column
     * @Assert\NotBlank()
     * @Assert\MinLength(8)
     */
    protected $password;

    /**
     * @ORM\Column
     */
    protected $salt;

    /**
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $registrationTime;

    /**
     *
     * @ORM\Column(type="string", nullable=true, length=50)
     */
    protected $registrationToken;

    /**
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $resetTime;

    /**
     *
     * @ORM\Column(type="string", nullable=true, length=50)
     */
    protected $resetToken;

    /**
     * @ORM\ManyToMany(targetEntity="Group", inversedBy="users")
     * @ORM\JoinTable(name="fom_users_groups")
     */
    protected $groups;

    public function __construct() {
        $this->groups = new ArrayCollection();
    }

    /**
     * Set id
     *
     * @param integer $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param string $username
     */
    public function setUsername($username) {
        $this->username = $username;
        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * Set email
     *
     * @param string $email
     */
    public function setEmail($email) {
        $this->email = $email;
        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password
     */
    public function setPassword($password) {
        $this->password = $password;
        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * Set salt
     *
     * @param string $salt
     */
    public function setSalt($salt) {
        $this->salt = $salt;
    }

    /**
     * Get salt
     *
     * @param string
     */
    public function getSalt() {
        return $this->salt;
    }

    /**
     * Set registrationTime
     *
     * @param text $registrationTime
     */
    public function setRegistrationTime($registrationTime)
    {
        $this->registrationTime = $registrationTime;
    }

    /**
     * Get registrationTime
     *
     * @return text
     */
    public function getRegistrationTime()
    {
        return $this->registrationTime;
    }

    /**
     * Set registrationToken
     *
     * @param text $registrationToken
     */
    public function setRegistrationToken($registrationToken)
    {
        $this->registrationToken = $registrationToken;
    }

    /**
     * Get registrationToken
     *
     * @return string
     */
    public function getRegistrationToken()
    {
        return $this->registrationToken;
    }

    /**
     * Set resetTime
     *
     * @param text $resetTime
     */
    public function setResetTime($resetTime)
    {
        $this->resetTime = $resetTime;
    }

    /**
     * Get resetTime
     *
     * @return text
     */
    public function getResetTime()
    {
        return $this->resetTime;
    }

    /**
     * Set resetToken
     *
     * @param text $resetToken
     */
    public function setResetToken($resetToken)
    {
        $this->resetToken = $resetToken;
    }

    /**
     * Get resetToken
     *
     * @return string
     */
    public function getResetToken()
    {
        return $this->resetToken;
    }

    /**
     * Add groups
     *
     * @param FOM\UserBundle\Entity\Group $groups
     */
    public function addGroups(Group $group) {
        $this->groups[] = $group;
        return $this;
    }

    /**
     * Get groups
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getGroups() {
        return $this->groups;
    }

    /**
     * Get role objects
     *
     * @return array
     */
    public function getRoles() {
        $roles = array();
        foreach($this->groups as $group) {
            $roles = array_merge($roles, $group->getRoles());
        }
        $roles[] = 'ROLE_USER';
        return $roles;
    }

    /**
     * Erase sensitive data like plain password. Don't fiddle with persisted data in here!
     */
    public function eraseCredentials() {
    }

    /**
     * Compare users
     *
     * This user class is only compatible with itself and compares the
     * username property. If you'r needs differ, use a subclass.
     *
     * @param UserInterface $user The user to compare
     */
    public function equals(UserInterface $user) {
        return (get_class() === get_class($user)
            && $this->getUsername() === $user->getUsername());
    }



    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return true;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function isEnabled()
    {
        return $this->registrationToken === null;
    }

    /**
     * Checks whether the user is an admin for the given type (or is superadmin
     * if type is omitted)
     *
     * @param string $type Type of admin to check
     */
    public function isAdmin($type = null)
    {
        if($this->getId() === 1) {
            return true;
        }

        //$role = ($type !== null ? 'ROLE_ADMIN_' .$type : 'ROLE_ADMIN');

        return false;
    }
}
