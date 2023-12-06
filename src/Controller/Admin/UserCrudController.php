<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Symfony\Component\Form\FormEvents;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Component\Form\FormBuilderInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    public function __construct(
        public UserPasswordHasherInterface $userPasswordHasher
    ) {}
    
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

/*  Cette méthode configure les actions qui sont disponibles pour l'entité.  */

    public function configureActions(Actions $actions): Actions{
        return $actions 
        ->add(Crud::PAGE_EDIT, Action::INDEX)
        ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ->add(Crud::PAGE_EDIT, Action::DETAIL)
        ;
    }

/* Cette méthode configure les champs qui seront affichés dans le formulaire de création/édition de l'utilisateur.*/
    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            ChoiceField::new('civility')->setChoices([
                'Monsieur' => 'Mr',
                'Madame' => 'Mme',
                'Mademoiselle' => 'Mlle'
            ]),
            TextField::new('full_name'),
            EmailField::new('email'),
            TextField::new('password')
            ->setFormType(RepeatedType::class)
            ->setFormTypeOptions([
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'Password',
                    'row_attr'=> [
                        'class'=>"col-md-6 col-xxl-5"
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirm Password',
                    'row_attr'=> [
                        'class'=>"col-md-6 col-xxl-5"
                    ],
                ],
                'mapped' => false,
            ])
            ->setRequired($pageName ===  Crud::PAGE_NEW)
            ->onlyOnForms(),
        ];
    }

/* Ces méthodes personnalisent la création des nouveaux formulaires pour l'ajout et la modification des utilisateurs.*/

    public function createNewFormBuilder(
        EntityDto $entityDto, 
        KeyValueStore $formOptions, 
        AdminContext $context): FormBuilderInterface
        {
        $formBuilder = parent::createNewFormBuilder($entityDto, $formOptions, $context);

        return $this->addPasswordEventListener($formBuilder);
    }
    public function createEditFormBuilder(
        EntityDto $entityDto, 
        KeyValueStore $formOptions, 
        AdminContext $context): FormBuilderInterface
        {
        $formBuilder = parent::createEditFormBuilder($entityDto, $formOptions, $context);

        return $this->addPasswordEventListener($formBuilder);
    }

/* Cette méthode ajoute un événement d'écoute à un formulaire pour écouter l'événement POST_SUBMIT.*/

    public function addPasswordEventListener(FormBuilderInterface $formBuilder){
        return $formBuilder->addEventListener(FormEvents::POST_SUBMIT, $this->hashPassword());
    }

/* : C'est une fonction de hachage qui est appelée lors de la soumission du formulaire. 
Elle récupère le mot de passe saisi, le hash à l'aide du service UserPasswordHasherInterface 
et le stocke dans la base de données pour l'utilisateur concerné*/

    public function hashPassword(){
        return function($event){
            $form = $event->getForm();
            if(!$form->isValid()){
                return;
            }

            $password = $form->get('password')->getData();

            if($password === null){
                return;
            }


            $user = $form->getData();
            $hash = $this->userPasswordHasher->hashPassword($user, $password);
            $form->getData()->setPassword($hash);
        };
    }
    
}
