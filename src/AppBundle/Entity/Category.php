<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Category
 *
 * @ORM\Table(name="category")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CategoryRepository")
 */
class Category
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
     * @Assert\NotBlank(message="カテゴリ名は必須です")
     * @Assert\Length(max=100, maxMessage="カテゴリ名は{{ limit }}文字以内で入力してください")
     * @ORM\Column(name="name", type="string", length=100)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", nullable=true)
     * @Assert\Length(max=1000, maxMessage="説明は{{ limit }}文字以内で入力してください")

     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="color", type="string", length=7)
     */
    private $color;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;

    /**
     * @var Collection|Memo[]
     *
     * @ORM\OneToMany(targetEntity="Memo", mappedBy="category")
     */
    private $memos;

    public function __construct() {
      $timezone = new \DateTimeZone('Asia/Tokyo');
      $this->createdAt = new \DateTime('now', $timezone);
      $this->memos = new ArrayCollection();
      $this->color = '#007bff';
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Category
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Category
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set color
     *
     * @param string $color
     *
     * @return Category
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get color
     *
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Category
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return Collection|Memo[]
     */
    public function getMemos(): Collection
    {
      return $this->memos;
    }

    public function addMemo(Memo $memo): self
    {
      if (!$this->memos->contains($memo)) {
        $this->memos[] = $memo;
        $memo->setCategory($this);
      }
      return $this;
    }

    public function removeMemo(Memo $memo):self
    {
      if ($this->memos->contains($memo)) {
        $this->memos->removeElement($memo);

        if ($memo->getCategory() === $this) {
          $memo->setCategory(null);
        }
      }
      return $this;
    }

    public function getMemoCount(): int {
      return $this->memos->count();
    }

    public function __toString(): string {
      return $this->name ?? '';
    }
}
