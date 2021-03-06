<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                "required" => true,
                "attr" => ["class" => "form-control mb-2"]
            ])
            ->add('image', FileType::class, [
                'mapped' => false,
                'label' => 'Image (PNG, JPG or JPEG)',
                'required' => false,
                'constraints' => [
                    new File([
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/jpg',
                            'image/png'
                        ],
                        'mimeTypesMessage' => "Please upload a valid image (PNG, JPG or JPEG)",
                    ])
                ],
                "attr" => ["class" => "form-control mb-2",]
            ])
            ->add('price', NumberType::class, ["attr" => [
                "class" => "form-control mb-2"
            ]])
            ->add('description', TextareaType::class, ['required' => false, "attr" => [
                "class" => "form-control mb-2"
            ]])
            ->add('category', EntityType::class, ['class' => Category::class, "attr" => [
                "class" => "form-select mb-2",
            ]]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
