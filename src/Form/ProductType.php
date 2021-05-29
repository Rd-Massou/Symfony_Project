<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, ["attr" => [
                "class" => "form-control mb-2"
            ]])
            ->add('image', FileType::class, ['required' => false, "attr" => [
                "class" => "form-control mb-2",
            ]])
            ->add('price', NumberType::class, ["attr" => [
                "class" => "form-control mb-2"
            ]])
            ->add('description', TextareaType::class, ['required' => false, "attr" => [
                "class" => "form-control mb-2"
            ]])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
