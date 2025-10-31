<?php

namespace ProductRankingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ProductRankingBundle\Repository\RankingListRepository;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

#[ORM\Table(name: 'product_ranking_list', options: ['comment' => '排行榜管理'])]
#[ORM\Entity(repositoryClass: RankingListRepository::class)]
class RankingList implements \Stringable
{
    use SnowflakeKeyAware;
    use TimestampableAware;
    use BlameableAware;

    #[IndexColumn]
    #[TrackColumn]
    #[Groups(groups: ['restful_read'])]
    #[Assert\NotNull]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    private ?bool $valid = false;

    #[Groups(groups: ['restful_read'])]
    #[Assert\NotNull]
    #[Assert\Length(min: 1, max: 64)]
    #[ORM\Column(type: Types::STRING, length: 64, options: ['comment' => '标题'])]
    private ?string $title = null;

    #[Groups(groups: ['restful_read'])]
    #[Assert\Length(max: 100)]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '副标题'])]
    private ?string $subtitle = null;

    #[Groups(groups: ['restful_read'])]
    #[Assert\NotNull]
    #[Assert\Length(max: 20)]
    #[ORM\Column(type: Types::STRING, length: 20, options: ['comment' => '颜色'])]
    private ?string $color = '';

    #[Groups(groups: ['restful_read'])]
    #[Assert\Length(max: 255)]
    #[Assert\Url]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => 'LOGO地址'])]
    private ?string $logoUrl = null;

    /**
     * @var Collection<int, RankingItem>
     */
    #[ORM\OneToMany(mappedBy: 'list', targetEntity: RankingItem::class, orphanRemoval: true)]
    private Collection $items;

    #[Groups(groups: ['restful_read'])]
    #[Assert\Length(max: 65535)]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '计算SQL'])]
    private ?string $scoreSql = null;

    #[Groups(groups: ['restful_read'])]
    #[Assert\PositiveOrZero]
    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '数量'])]
    private ?int $count = null;

    /**
     * @var Collection<int, RankingPosition>
     */
    #[ORM\ManyToMany(targetEntity: RankingPosition::class, inversedBy: 'lists', fetch: 'EXTRA_LAZY')]
    private Collection $positions;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->positions = new ArrayCollection();
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

    public function setTitle(string $title): void
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

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function setSubtitle(?string $subtitle): void
    {
        $this->subtitle = $subtitle;
    }

    public function getLogoUrl(): ?string
    {
        return $this->logoUrl;
    }

    public function setLogoUrl(?string $logoUrl): void
    {
        $this->logoUrl = $logoUrl;
    }

    /**
     * @return Collection<int, RankingItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(RankingItem $item): void
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setList($this);
        }
    }

    public function removeItem(RankingItem $item): void
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getList() === $this) {
                $item->setList(null);
            }
        }
    }

    public function getScoreSql(): ?string
    {
        return $this->scoreSql;
    }

    public function setScoreSql(?string $scoreSql): void
    {
        $this->scoreSql = $scoreSql;
    }

    public function getCount(): ?int
    {
        return $this->count;
    }

    public function setCount(?int $count): void
    {
        $this->count = $count;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): void
    {
        $this->color = $color;
    }

    /**
     * @return Collection<int, RankingPosition>
     */
    public function getPositions(): Collection
    {
        return $this->positions;
    }

    public function addPosition(RankingPosition $position): void
    {
        if (!$this->positions->contains($position)) {
            $this->positions->add($position);
        }
    }

    public function removePosition(RankingPosition $position): void
    {
        $this->positions->removeElement($position);
    }
}
