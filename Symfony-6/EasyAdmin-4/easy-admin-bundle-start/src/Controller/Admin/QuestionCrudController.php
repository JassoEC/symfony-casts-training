<?php

namespace App\Controller\Admin;

use App\EasyAdmin\VotesField;
use App\Entity\Question;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

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

        yield Field::new('name');

        yield AssociationField::new('topic');

        yield TextareaField::new('question')
            ->hideOnIndex();

        yield VotesField::new('votes')
            ->setLabel('Total Votes')
            ->setTextAlign('right');

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
    }
}
