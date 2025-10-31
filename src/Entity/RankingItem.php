<?php

namespace ProductRankingBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ProductRankingBundle\Repository\RankingItemRepository;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

#[ORM\Table(name: 'product_ranking_item', options: ['comment' => '商品排行'])]
#[ORM\Entity(repositoryClass: RankingItemRepository::class)]
#[ORM\UniqueConstraint(name: 'product_ranking_item_uniq_1', columns: ['list_id', 'number'])]
#[ORM\UniqueConstraint(name: 'product_ranking_item_uniq_2', columns: ['list_id', 'spu_id'])]
class RankingItem implements \Stringable
{
    use SnowflakeKeyAware;
    use TimestampableAware;
    use BlameableAware;

    #[IndexColumn]
    #[TrackColumn]
    #[Assert\NotNull]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    private ?bool $valid = false;

    #[ORM\ManyToOne(inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private ?RankingList $list = null;

    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    #[ORM\Column(options: ['comment' => '排名'])]
    private ?int $number = null;

    /**
     * @var string|null SPU ID，字符串类型以兼容各种ID格式
     */
    #[Groups(groups: ['restful_read'])]
    #[Assert\NotNull]
    #[Assert\Length(min: 1, max: 191)]
    #[ORM\Column(type: Types::STRING, length: 191, options: ['comment' => 'SPU ID'])]
    private ?string $spuId = null;

    #[Assert\Length(max: 500)]
    #[ORM\Column(length: 500, nullable: true, options: ['comment' => '上榜理由'])]
    private ?string $textReason = null;

    #[Assert\PositiveOrZero]
    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '分数'])]
    private ?int $score = null;

    #[Assert\NotNull]
    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: ['comment' => '固定排名'])]
    private ?bool $fixed = false;

    #[Assert\Length(max: 255)]
    #[Assert\Url]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '推荐人头像'])]
    private ?string $recommendThumb = null;

    #[Assert\Length(max: 65535)]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '推荐理由'])]
    private ?string $recommendReason = null;

    public function __toString(): string
    {
        return "{$this->getList()?->getTitle()} - {$this->getNumber()}";
    }

    public function getList(): ?RankingList
    {
        return $this->list;
    }

    public function setList(?RankingList $list): void
    {
        $this->list = $list;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): void
    {
        $this->number = $number;
    }

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): void
    {
        $this->valid = $valid;
    }

    public function getTextReason(): ?string
    {
        return $this->textReason;
    }

    public function setTextReason(?string $textReason): void
    {
        $this->textReason = $textReason;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): void
    {
        $this->score = $score;
    }

    public function getSpuId(): ?string
    {
        return $this->spuId;
    }

    public function setSpuId(string $spuId): void
    {
        $this->spuId = $spuId;
    }

    public function isFixed(): ?bool
    {
        return $this->fixed;
    }

    public function setFixed(?bool $fixed): void
    {
        $this->fixed = $fixed;
    }

    public function getRecommendThumb(): ?string
    {
        return $this->recommendThumb;
    }

    public function setRecommendThumb(?string $recommendThumb): void
    {
        $this->recommendThumb = $recommendThumb;
    }

    public function getRecommendReason(): ?string
    {
        return $this->recommendReason;
    }

    public function setRecommendReason(?string $recommendReason): void
    {
        $this->recommendReason = $recommendReason;
    }
}
