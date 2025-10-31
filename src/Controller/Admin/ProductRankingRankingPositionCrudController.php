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
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use ProductRankingBundle\Entity\RankingPosition;

/**
 * @extends AbstractCrudController<RankingPosition>
 */
#[AdminCrud(routePath: '/product-ranking/ranking-position', routeName: 'product_ranking_ranking_position')]
final class ProductRankingRankingPositionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return RankingPosition::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('排行榜位置')
            ->setEntityLabelInPlural('排行榜位置列表')
            ->setPageTitle('index', '排行榜位置管理')
            ->setPageTitle('detail', '排行榜位置详情')
            ->setPageTitle('edit', '编辑排行榜位置')
            ->setPageTitle('new', '创建排行榜位置')
            ->setHelp('index', '管理排行榜的展示位置信息')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['id', 'title'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->setMaxLength(9999);
        yield BooleanField::new('valid', '是否有效');
        yield TextField::new('title', '位置名称');
        yield AssociationField::new('lists', '关联排行榜')
            ->setFormTypeOptions([
                'by_reference' => false,
            ])
        ;
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
            ->add(TextFilter::new('title', '位置名称'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }
}
