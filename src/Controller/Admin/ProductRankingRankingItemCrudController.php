<?php

declare(strict_types=1);

namespace ProductRankingBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use ProductRankingBundle\Entity\RankingItem;

/**
 * @extends AbstractCrudController<RankingItem>
 */
#[AdminCrud(routePath: '/product-ranking/ranking-item', routeName: 'product_ranking_ranking_item')]
final class ProductRankingRankingItemCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return RankingItem::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('排行榜商品')
            ->setEntityLabelInPlural('排行榜商品列表')
            ->setPageTitle('index', '排行榜商品管理')
            ->setPageTitle('detail', '排行榜商品详情')
            ->setPageTitle('edit', '编辑排行榜商品')
            ->setPageTitle('new', '创建排行榜商品')
            ->setHelp('index', '管理排行榜中的商品排名信息')
            ->setDefaultSort(['number' => 'ASC'])
            ->setSearchFields(['id', 'spuId', 'number'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->setMaxLength(9999);
        yield BooleanField::new('valid', '是否有效');
        yield AssociationField::new('list', '所属排行榜');
        yield IntegerField::new('number', '排名');
        yield TextField::new('spuId', 'SPU ID');
        yield TextareaField::new('textReason', '上榜理由')->hideOnIndex();
        yield IntegerField::new('score', '分数');
        yield BooleanField::new('fixed', '固定排名');
        yield TextField::new('recommendThumb', '推荐人头像')->hideOnIndex();
        yield TextareaField::new('recommendReason', '推荐理由')->hideOnIndex();
        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;
        yield DateTimeField::new('updateTime', '更新时间')
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->reorder(Crud::PAGE_INDEX, [Action::DETAIL])
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('valid', '是否有效'))
            ->add(EntityFilter::new('list', '所属排行榜'))
            ->add(NumericFilter::new('number', '排名'))
            ->add(TextFilter::new('spuId', 'SPU ID'))
            ->add(NumericFilter::new('score', '分数'))
            ->add(BooleanFilter::new('fixed', '固定排名'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }
}
