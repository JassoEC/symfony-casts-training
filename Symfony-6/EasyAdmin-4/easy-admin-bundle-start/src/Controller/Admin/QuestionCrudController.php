<?php

namespace App\Controller\Admin;

use App\EasyAdmin\VotesField;
use App\Entity\Question;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[IsGranted('ROLE_MODERATOR')]
class QuestionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Question::class;
    }


    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->onlyOnIndex();

        yield Field::new('slug')
            ->hideOnIndex()
            ->setFormTypeOption('disabled', $pageName !== Crud::PAGE_NEW);

        yield Field::new('name')
            ->setSortable(false);

        yield AssociationField::new('topic');

        yield TextEditorField::new('question')
            ->hideOnIndex();

        yield VotesField::new('votes')
            ->setLabel('Total Votes')
            ->setTextAlign('right')
            ->setPermission('ROLE_SUPER_ADMIN');

        yield AssociationField::new('askedBy')
            ->autocomplete()
            ->formatValue(static function ($value, Question $question): ?string {
                if (!$user = $question->getAskedBy()) {
                    return null;
                }
                return sprintf('%s&nbsp;(%s)', $user->getEmail(), $user->getQuestions()->count());
            });

        yield AssociationField::new('answers')
            ->autocomplete()
            ->setFormTypeOption('by_reference', false);

        yield Field::new('createdAt')
            ->hideOnForm();

        yield AssociationField::new('updatedBy')
            ->onlyOnDetail();

        yield Field::new('isApproved');
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setDefaultSort([
                'askedBy.enabled' => 'DESC',
                'createdAt' => 'DESC'
            ]);
    }

    public function configureActions(Actions $actions): Actions
    {

        $viewAction = Action::new('view')
            ->linkToUrl(function (Question $question): string {
                return $this->generateUrl('app_question_show', ['slug' => $question->getSlug()]);
            })
            ->setIcon('fa fa-eye')
            ->setCssClass('btn btn-success')
            ->setLabel('View on site');

        return parent::configureActions($actions)

            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                $action->displayIf(static function (Question $question) {
                    //  return !$question->getIsApproved();
                    return true;
                });

                return $action;
            })

            ->setPermission(Action::INDEX, 'ROLE_MODERATOR')
            ->setPermission(Action::DETAIL, 'ROLE_MODERATOR')
            ->setPermission(Action::EDIT, 'ROLE_MODERATOR')

            ->setPermission(Action::NEW, 'ROLE_SUPER_ADMIN')
            ->setPermission(Action::DELETE, 'ROLE_SUPER_ADMIN')
            ->setPermission(Action::BATCH_DELETE, 'ROLE_SUPER_ADMIN')

            ->add(Crud::PAGE_DETAIL, $viewAction)
            ->add(Crud::PAGE_INDEX, $viewAction);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return parent::configureFilters($filters)
            ->add('topic')
            ->add('votes')
            ->add('createdAt')
            ->add('name');
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new \LogicException('The user must be an instance of ' . User::class);
        }

        $entityInstance->setUpdatedBy($user);

        parent::updateEntity($entityManager, $entityInstance);
    }
    /**
     * @param Question $entityInstance
     */

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {

        if ($entityInstance->getIsApproved()) {
            throw new \LogicException('You cannot delete an approved question');
        }

        parent::deleteEntity($entityManager, $entityInstance);
    }
}
