<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Memo
 *
 * @ORM\Table(name="memo")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MemoRepository")
 */
class Memo
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
   * @Assert\NotBlank(message="タイトルは必須です")
   * @Assert\Length(max=255, maxMessage="タイトルは{{ limit }}文字以内で入力してください")
   * @ORM\Column(name="title", type="string", length=255)
   */
  private $title;

  /**
   * @var string
   * @Assert\NotBlank(message="内容は必須です")
   * @ORM\Column(name="content", type="text")
   */
  private $content;

  /**
   * @var \DateTime
   *
   * @ORM\Column(name="createdAt", type="datetime")
   */
  private $createdAt;


  ## カテゴリーの紐付け
  /**
   * @var Category|null
   *
   * @ORM\ManyToOne(targetEntity="Category", inversedBy="memos")
   * @ORM\JoinColumn(nullable=true)
   */
  private $category;

  public function __construct()
  {
    $timezone = new \DateTimeZone('Asia/Tokyo');
    $this->createdAt = new \DateTime('now', $timezone);
    $this->tags = new ArrayCollection();
  }

  public function getCategory(): ?Category
  {
    return $this->category;
  }

  public function setCategory(?Category $category = null)
  {
    $this->category = $category;
    return $this;
  }

  ## Userの紐付け

  /**
   * @var User|null
   *
   * @ORM\ManyToOne(targetEntity="User", inversedBy="memos")
   * @ORM\JoinColumn(nullable=true)
   */
  private $author;

  /**
   * @var Collection|Tag[]
   * @ORM\ManyToMany(targetEntity="Tag", inversedBy="memos")
   * @ORM\JoinTable(name="memo_tag",
   *   joinColumns={@ORM\JoinColumn(name="memo_id", referencedColumnName="id")},
   *   inverseJoinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="id")}
   * )
   */
  private $tags;

  /**
   * get author
   *
   * @return User|null
   */
  public function getAuthor(): ?User
  {
    return $this->author;
  }

  /**
   * set author
   *
   * @param User|null $author
   *
   * @return Memo
   */
  public function setAuthor(?User $author = null): self
  {
    $this->author = $author;
    return $this;
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
   * Set title
   *
   * @param string $title
   *
   * @return Memo
   */
  public function setTitle($title)
  {
    $this->title = $title;
    return $this;
  }

  /**
   * Get title
   *
   * @return string
   */
  public function getTitle()
  {
    return $this->title;
  }

  /**
   * Set content
   *
   * @param string $content
   *
   * @return Memo
   */
  public function setContent($content)
  {
    $this->content = $content;
    return $this;
  }

  /**
   * Get content
   *
   * @return string
   */
  public function getContent()
  {
    return $this->content;
  }

  /**
   * Set createdAt
   *
   * @param \DateTime $createdAt
   *
   * @return Memo
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
   * Get tags
   *
   * @return Collection|Tag[]
   */
  public function getTags(): Collection
  {
    return $this->tags;
  }

  /**
   * Add tag
   *
   * @param Tag $tag
   * @return Memo
   */
  public function addTag(Tag $tag): self
  {
    if (!$this->tags->contains($tag)) {
      $this->tags[] = $tag;
    }
    return $this;
  }

  /**
   * Remove tag
   *
   * @param Tag $tag
   * @return Memo
   */
  public function removeTag(Tag $tag): self
  {
    $this->tags->removeElement($tag);
    return $this;
  }
}
