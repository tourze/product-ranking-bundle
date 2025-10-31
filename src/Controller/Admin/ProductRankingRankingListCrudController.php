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
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use ProductRankingBundle\Entity\RankingList;

/**
 * @extends AbstractCrudController<RankingList>
 */
#[AdminCrud(routePath: '/product-ranking/ranking-list', routeName: 'product_ranking_ranking_list')]
final class ProductRankingRankingListCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return RankingList::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('排行榜')
            ->setEntityLabelInPlural('排行榜列表')
            ->setPageTitle('index', '排行榜管理')
            ->setPageTitle('detail', '排行榜详情')
            ->setPageTitle('edit', '编辑排行榜')
            ->setPageTitle('new', '创建排行榜')
            ->setHelp('index', '管理所有排行榜信息')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['id', 'title', 'subtitle'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->setMaxLength(9999);
        yield BooleanField::new('valid', '是否有效');
        yield TextField::new('title', '标题');
        yield TextField::new('subtitle', '副标题');
        yield ColorField::new('color', '颜色');
        yield TextField::new('logoUrl', 'LOGO地址')->hideOnIndex();
        yield TextField::new('scoreSql', '计算SQL')->hideOnIndex();
        yield IntegerField::new('count', '数量');
        yield AssociationField::new('positions', '展示位置')
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
            ->add(TextFilter::new('title', '标题'))
            ->add(TextFilter::new('subtitle', '副标题'))
            ->add(EntityFilter::new('positions', '展示位置'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }
}
