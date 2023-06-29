<?php

namespace App\Controller\Admin;

use App\Entity\Question;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

class QuestionPendingApprovalCrudController extends QuestionCrudController
{

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setPageTitle(Crud::PAGE_INDEX, 'Question Pending Approval')
            ->setPageTitle(Crud::PAGE_DETAIL, static fn (Question $question) => sprintf('#%s %s', $question->getId(), $question->getName()))
            ->setHelp(Crud::PAGE_INDEX, 'Questions are not published until they are approved by an admin or moderator.');
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->andWhere('entity.isApproved = :approved')
            ->setParameter('approved', false);
    }
}
