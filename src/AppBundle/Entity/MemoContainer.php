<?php
  namespace AppBundle\Entity\MemoContainer;

  use Doctrine\ORM\Mapping as ORM;
  use Doctrine\Common\Collections\ArrayCollection;
  use Doctrine\Common\Collections\Collection;

  /**
   * Container
   *
   * ユーザーとメモの集約を管理するエンティティ
   *
   * @ORM\Table(name="container")
   * @ORM\Enitty(repositoryClass="AppBundle\Repository\ContainerRepository")
   */

  class Container
  {
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\id
     * @ORM\GeneratedValue(strategy="AUTO")
     */

    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string|null;
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var User|null;
     *
     * @ORM\ManyToOne(targetEntity="Memo")
     * @ORM\JoinTable(name="container_memos")
     * joinColumns={@ORM\JoinColumn(name="container_id", referencedColumnName="id")},
     * inverseJoinColumns={@ORM\JoinColumn(name="memo_id", referencedColumnName="id")}
     */

    private $memos;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;

    public function __construct()
    {
      $this->memos = new ArrayCollection();
      $timezone = new \DateTimeZone('Asia/Tokyo');
      $this->createdAt = \DateTime('now', $timezone);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     *
     * @return Container
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string|null $description
     *
     * @return Container
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param User|null $owner
     *
     * @return Container
     */
    public function setOwner(?User $owner = null)
    {
        $this->owner = $owner;
        return $this;
    }

    /**
     * @return User|null
     */
    public function getOwner(): ?User
    {
        return $this->owner;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    // ====================================
    // メモとの関連管理（Railsのassociationメソッド的な）
    // ====================================

    /**
     * @return Collection|Memo[]
     */
    public function getMemos(): Collection
    {
        return $this->memos;
    }

    /**
     * @param Memo $memo
     *
     * @return Container
     */
    public function addMemo(Memo $memo): self
    {
        if (!$this->memos->contains($memo)) {
            $this->memos[] = $memo;
        }
        return $this;
    }

    /**
     * @param Memo $memo
     *
     * @return MemoContainer
     */
    public function removeMemo(Memo $memo): self
    {
        if ($this->memos->contains($memo)) {
            $this->memos->removeElement($memo);
        }
        return $this;
    }

    // ====================================
    // シンプルな計算メソッド（Entityでも許容範囲）
    // 注意：複雑なビジネスロジックはServiceに書く！
    // ====================================

    /**
     * @return int
     */
    public function getMemoCount(): int
    {
        return count($this->memos);
    }

    /**
     * @return bool
     */
    public function hasMemos(): bool
    {
        return !$this->memos->isEmpty();
    }
  }
