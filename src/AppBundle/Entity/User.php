<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 */
class User
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255)
     */
    private $password;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;

    /**
     * @var array
     *
     * @ORM\Column(name="roles", type="json")
     */
    private $roles;

    /**
     * @var Collection|Memo[]
     * @ORM\oneToMany(targetEntity="Memo", mappedBy="author")
     */

    private $memos;

    public function __construct() {
      $this->memos = new ArrayCollection();
      $timezone = new \DateTimeZone('Asia/Tokyo');
      $this->createdAt = new \DateTime('now', $timezone);
      $this->roles = ['ROLE_MEMBER'];
    }

    /**
     * @return Collection|Memo[]
     */
    public function getMemos(): Collection {
      return $this->memos;
    }

    public function addMemo(Memo $memo): self {
      if(!$this->memos->contains($memo)) {
        $this->memos[] = $memo;
        $memo->setAuthor($this);
      }
      return $this;
    }

    public function removeMemo(Memo $memo): self {
      if($this->memos->contains($memo)) {
        $this->memos->removeElement($memo);
        if($memo->getAuthor() === $this) {
          $memo->setAuthor(null);
        }
      }
      return $this;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password.
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return User
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Get roles.
     *
     * @return array
     */
    public function getRoles()
    {
        $roles = $this->roles;
        // ユーザーは最低でも1つのロールを持つ必要がある
        if (empty($roles)) {
            $roles[] = 'ROLE_MEMBER';
        }
        return array_unique($roles);
    }

    /**
     * Set roles.
     *
     * @param array $roles
     *
     * @return User
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Add role.
     *
     * @param string $role
     *
     * @return User
     */
    public function addRole($role)
    {
        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * Remove role.
     *
     * @param string $role
     *
     * @return User
     */
    public function removeRole($role)
    {
        $key = array_search($role, $this->roles, true);
        if ($key !== false) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    /**
     * Check if user has a specific role.
     *
     * @param string $role
     *
     * @return bool
     */
    public function hasRole($role)
    {
        return in_array($role, $this->getRoles(), true);
    }

    /**
     * Check if user is an admin.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->hasRole('ROLE_ADMIN');
    }
}
