<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MemoContainerRepository")
 * @ORM\Table(name="memo_container")
 */
class MemoContainer
{
  /**
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   * @ORM\Column(type="integer")
   */
  private $id;

  /**
   * @ORM\Column(type="string", length=255)
   */
  private $name;

  /**
   * @ORM\Column(type="text", nullable=true)
   */
  private $description;

  /**
   * @ORM\Column(type="datetime")
   */
  private $createdAt;

  /**
   * @ORM\ManyToOne(targetEntity="User")
   * @ORM\JoinColumn(nullable=true)
   */
  private $user;

  /**
   * @ORM\ManyToMany(targetEntity="Memo")
   * @ORM\JoinTable(name="memo_container_memos")
   */
  private $memos;

  public function __construct()
  {
    $timezone = new \DateTimeZone('Asia/Tokyo');
    $this->createdAt = new \DateTime('now', $timezone);
    $this->memos = new ArrayCollection();
  }

  /**
   * @return int
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * @return string|null
   */
  public function getDescription()
  {
    return $this->description;
  }

  /**
   * @return \DateTime
   */
  public function getCreatedAt()
  {
    return $this->createdAt;
  }

  /**
   * @return User|null
   */
  public function getUser()
  {
    return $this->user;
  }

  /**
   * @return Collection|Memo[]
   */
  public function getMemos(): Collection
  {
    return $this->memos;
  }

  /**
   * @param string $name
   * @return MemoContainer
   */
  public function setName($name)
  {
    $this->name = $name;
    return $this;
  }

  /**
   * @param string|null $description
   * @return MemoContainer
   */
  public function setDescription($description)
  {
    $this->description = $description;
    return $this;
  }

  /**
   * @param User|null $user
   * @return MemoContainer
   */
  public function setUser(?User $user = null)
  {
    $this->user = $user;
    return $this;
  }

  /**
   * @param Memo $memo
   * @return MemoContainer
   */
  public function addMemo(Memo $memo)
  {
    if (!$this->memos->contains($memo)) {
      $this->memos[] = $memo;
    }
    return $this;
  }

  /**
   * @param Memo $memo
   * @return MemoContainer
   */
  public function removeMemo(Memo $memo)
  {
    $this->memos->removeElement($memo);
    return $this;
  }

  /**
   * @return int
   */
  public function getMemoCount(): int
  {
    return $this->memos->count();
  }

  /**
   * @return bool
   */
  public function isEmpty(): bool
  {
    return $this->getMemoCount() === 0;
  }

  /**
   * @param Category $category
   * @return int
   */
  public function getMemoCountByCategory(Category $category): int
  {
    $count = 0;
    foreach ($this->memos as $memo) {
      if ($memo->getCategory() === $category) {
        $count++;
      }
    }
    return $count;
  }
}
