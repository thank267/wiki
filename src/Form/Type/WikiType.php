<?php
namespace App\Form\Type;

use App\Entity\Wiki;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

use Symfony\Component\String\Slugger\AsciiSlugger;

use function Symfony\Component\String\u;

class WikiType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {   

       
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $wiki = $event->getData();
            $form = $event->getForm();
   
            if (!$wiki || null === $wiki->getId()) {
                $form->add('address', TextType::class, ['required' => false]);
            }

            $form->add('title', TextType::class);
            $form->add('description', TextareaType::class);
            if (!$wiki || null === $wiki->getId()) {
                $form->add('save', SubmitType::class, ['label' => 'Создать wiki']);
            }
            else {
                $form->add('save', SubmitType::class, ['label' => 'Изменить wiki']);
            }
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $wiki = $event->getData();

            $slugger = new AsciiSlugger('ru');

            if (isset($wiki['address']) && !$wiki['address'] && null === $wiki['id']){

                $wiki['address'] = $wiki['title'];
                $wiki['address'] = u($slugger->slug($wiki['address'])->toString())->replace("-","_")->lower();
  
            }

            

            $event->setData($wiki);
 
        });

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Wiki::class,
        ]);

       

    }
}