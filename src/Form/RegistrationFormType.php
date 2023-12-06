<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            
            ->add('full_name', TextType::class, [
                'label' => false,
                'attr'=> [
                    'placeholder' =>'Entrez Votre Nom Complet',
                    'class' => 'form-control'],
                'row_attr'=> [
                    'class' => 'form-group mb-3'
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => false,
                'attr'=> [
                    'placeholder' =>'Entrez Votre E-mail',
                    'class' => 'form-control'],
                'row_attr'=> [
                    'class' => 'form-group mb-3'
                ]
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => "Termes et Conditons d'utilisations",
                'mapped' => false,
                'attr' =>[
                    'class'=>'form-check-input',
                    
                ],
                'constraints' => [
                    new IsTrue([
                        'message' => "Acceptez Nos Termes d'Utilisation",
                    ]),
                ],
                'row_attr'=> [
                    'class'=>'custome-checkbox d-flex gap-2'
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'type'=> PasswordType::class,
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Entrez Votre Mot de Passe',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre Mot de passe doit contenir au moins {{ limit }} charactères',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
                    'first_options'=>[
                        'label'=>false,
                        'attr'=>[
                            'placeholder'=>'Entrez Votre Mot de Passe',
                            'class'=>'form-control'
                        ],
                        'row_attr'=> [
                            'class' => 'form-group mb-3'
                        ]
                        ],
                    'second_options'=>[
                        'label'=>false,
                        'attr'=>[
                            'placeholder'=>'Comfirmez Votre Mot de Passe',
                            'class'=>'form-control'
                        ],
                        'row_attr'=> [
                            'class' => 'form-group mb-3'
                        ]
                    ]
                ,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
