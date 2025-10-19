<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Tag
 *
 * @ORM\Table(name="tag")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TagRepository")
 */

class Tag
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
   * @Assert\NotBlank(message="タグ名は必須です")
   * @Assert\Length(max=50, maxMessage="タグ名は {{ limit }}文字以内で入力してください")
   * @ORM\Column(name="name", type="string", length=50, unique=true)
   */
  private $name;

  /**
   * @var string;
   * @ORM\Column(name="color", type="string", length=7)
   */

  private $color;

  /**
   * @var \DateTime
   * @ORM\Column(name="createdAt", type="datetime")
   */
  private $createdAt;

  /**
   * @var Collection|Memo[]
   * @ORM\ManyToMany(targetEntity="Memo", mappedBy="tags")
   */
  private $memos;

  public function __construct()
  {
    $timezone = new \DateTimeZone('Asia/Tokyo');
    $this->createdAt = new \DateTime('now', $timezone);
    $this->memos = new ArrayCollection();
    $this->color = '#28a745'; // デフォルトカラー
  }

  /**
   * Get id
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Set name
   */
  public function setName($name)
  {
    $this->name = $name;
    return $this;
  }

  /**
   * Get name
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Set color
   */
  public function setColor($color)
  {
    $this->color = $color;
    return $this;
  }

  /**
   * Get color
   */
  public function getColor()
  {
    return $this->color;
  }

  /**
   * Set createdAt
   */
  public function setCreatedAt($createdAt)
  {
    $this->createdAt = $createdAt;
    return $this;
  }

  /**
   * Get createdAt
   */
  public function getCreatedAt()
  {
    return $this->createdAt;
  }

  /**
   * Get memos
   */
  public function getMemos(): Collection
  {
    return $this->memos;
  }

  /**
   * メモ数を取得
   */
  public function getMemoCount(): int
  {
    return $this->memos->count();
  }
}
