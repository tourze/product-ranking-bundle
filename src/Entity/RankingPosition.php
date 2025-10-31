<?php

namespace ProductRankingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ProductRankingBundle\Repository\RankingPositionRepository;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

#[ORM\Table(name: 'product_ranking_position', options: ['comment' => '排行榜位置'])]
#[ORM\Entity(repositoryClass: RankingPositionRepository::class)]
class RankingPosition implements \Stringable
{
    use SnowflakeKeyAware;
    use TimestampableAware;
    use BlameableAware;

    #[IndexColumn]
    #[TrackColumn]
    #[Assert\NotNull]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    private ?bool $valid = false;

    #[Assert\Length(max: 50)]
    #[ORM\Column(type: Types::STRING, length: 50, unique: true, nullable: true, options: ['comment' => '名称'])]
    private ?string $title = null;

    /**
     * @var Collection<int, RankingList>
     */
    #[Ignore]
    #[ORM\ManyToMany(targetEntity: RankingList::class, mappedBy: 'positions', fetch: 'EXTRA_LAZY')]
    private Collection $lists;

    public function __construct()
    {
        $this->lists = new ArrayCollection();
    }

    public function __toString(): string
    {
        if (null === $this->getId()) {
            return '';
        }

        return "{$this->getTitle()}";
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): void
    {
        $this->valid = $valid;
    }

    /**
     * @return Collection<int, RankingList>
     */
    public function getLists(): Collection
    {
        return $this->lists;
    }

    public function addList(RankingList $list): void
    {
        if (!$this->lists->contains($list)) {
            $this->lists->add($list);
            $list->addPosition($this);
        }
    }

    public function removeList(RankingList $list): void
    {
        if ($this->lists->removeElement($list)) {
            $list->removePosition($this);
        }
    }
}
