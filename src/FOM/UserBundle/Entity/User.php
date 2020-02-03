<?php
namespace FOM\UserBundle\Entity;

use Doctrine\Common\Collections\Collection;
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
 * @ORM\MappedSuperclass()
 */
class User implements AdvancedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=false, length=255, unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(min=3)
     */
    protected $username;

    /**
     * @ORM\Column(type="string", nullable=false, length=255, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    protected $email;

    /**
     * Password HASH, not verbatim password
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=false)
     */
    protected $password;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $salt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $registrationTime;

    /**
     * @ORM\Column(type="string", nullable=true, length=50)
     */
    protected $registrationToken;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $resetTime;

    /**
     * @ORM\Column(type="string", nullable=true, length=50)
     */
    protected $resetToken;

    /**
     * @ORM\ManyToMany(targetEntity="Group", inversedBy="users")
     * @ORM\JoinTable(name="fom_users_groups")
     */
    protected $groups;

    /**
     * The profile is not stored here, but a placeholder is needed
     */
    protected $profile;

    /**
     *  Constructor
     */
    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

    /**
     * Set id
     *
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set salt
     *
     * @param string $salt
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
    }

    /**
     * Get salt
     *
     * @param string
     * @return null|string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set registrationTime
     *
     * @param string $registrationTime
     */
    public function setRegistrationTime($registrationTime)
    {
        $this->registrationTime = $registrationTime;
    }

    /**
     * Get registrationTime
     *
     * @return \DateTime
     */
    public function getRegistrationTime()
    {
        return $this->registrationTime;
    }

    /**
     * Set registrationToken
     *
     * @param string $registrationToken
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
     * @param string $resetTime
     */
    public function setResetTime($resetTime)
    {
        $this->resetTime = $resetTime;
    }

    /**
     * Get resetTime
     *
     * @return \DateTime
     */
    public function getResetTime()
    {
        return $this->resetTime;
    }

    /**
     * Set resetToken
     *
     * @param string $resetToken
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
     * Add to group
     *
     * @param Group $group
     * @return User
     */
    public function addGroup(Group $group)
    {
        $this->groups[] = $group;

        return $this;
    }

    /**
     * Get groups
     *
     * @return Collection|Group[]
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Get role objects
     *
     * @return array
     */
    public function getRoles()
    {
        $roles = array();
        foreach ($this->getGroups() as $group) {
            $roles[] = $group->getRole();
        }
        $roles[] = 'ROLE_USER';
        return $roles;
    }

    /**
     * Erase sensitive data like plain password. Don't fiddle with persisted data in here!
     */
    public function eraseCredentials()
    {
    }

    /**
     * Compare users
     *
     * This user class is only compatible with itself and compares the
     * username property. If you'r needs differ, use a subclass.
     *
     * @param UserInterface $user The user to compare
     * @return bool
     */
    public function equals(UserInterface $user)
    {
        return (get_class() === get_class($user)
            && $this->getUsername() === $user->getUsername());
    }

    /**
     * @return bool
     */
    public function isAccountNonExpired()
    {
        if ($this->profile && method_exists($this->profile, 'isAccountNonExpired')) {
            return $this->profile->isAccountNonExpired();
        }
        return true;
    }

    /**
     * @return bool
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        if ($this->profile && method_exists($this->profile, 'isEnabled')) {
            return $this->profile->isEnabled();
        }
        return $this->registrationToken === null;
    }

    /**
     * Checks if user is root. Only used to identify fallback owner identity in
     * CLI operations.
     *
     * @return bool
     * @internal
     */
    public function isAdmin()
    {
        if ($this->getId() === 1) {
            return true;
        }
        return false;
    }

    /**
     * @param BasicProfile|null $profile
     * @return $this
     */
    public function setProfile($profile)
    {
        if ($profile && \method_exists($profile, 'setUid')) {
            $profile->setUid($this);
        }
        $this->profile = $profile;
        return $this;
    }

    /**
     * @return BasicProfile|null
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Remove groups
     *
     * @param Group $groups
     */
    public function removeGroup(Group $groups)
    {
        $this->groups->removeElement($groups);
    }

    /**
     * @return bool
     */
    public function hasProfile()
    {
        return $this->getProfile() != null;
    }

    /**
     * Checks whether the user is locked.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a LockedException and prevent login.
     *
     * @return bool true if the user is not locked, false otherwise
     *
     * @see LockedException
     */
    public function isAccountNonLocked()
    {
        return true;
    }
    
    public function __toString()
    {
        return $this->getUsername() ?: '';
    }
}
