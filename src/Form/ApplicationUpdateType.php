<?php

namespace App\Form;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use App\Entity\Application;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ApplicationUpdateType extends AbstractType
{
    private const STATUS_TRANSITION_LABELS = [
        'created' => 'Created',
        'in_progress' => 'InProgress',
        'done' => 'Done',
        'complited' => 'Complited',
    ];

    public function __construct(private Security $security, private WorkflowInterface $applicationStateMachine,)
        
    {
        
    }
     public function buildForm(FormBuilderInterface $builder, array $options): void
    {   
        
        $application = $options['data'];
        $enabledTransitions = $this->applicationStateMachine->getEnabledTransitions($application);
        $choices = [];
        foreach ($enabledTransitions as $transition) {
            $choices[self::STATUS_TRANSITION_LABELS[$transition->getName()]] = $transition->getName();
        }
        
        $roles = $this->security->getUser()->getRoles();
        $status = $application->getStatusAsString();

        $builder
            ->add('transitionName', ChoiceType::class,
            [
                'choices' => $choices,
                'mapped' => false,
                'label' => 'Change status to:',
            ]);


            if (in_array('ROLE_ADMIN', $roles)) {
                $builder->add('approved', CheckboxType::class, [
                    'label'    => 'Approved',
                    'required' => false,
                ]);
                if ($status !== 'done' && $status !== 'complited') {
                    $builder->add('submit', SubmitType::class);
                }
            }


            if (in_array('ROLE_USER', $roles)) {
                $builder->add('submit', SubmitType::class);

        }


        $applicationStateMachine = $this->applicationStateMachine;
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event)
            use ($application, $applicationStateMachine
            ) {
            $data = $event->getData();
            $transitionName = $data['transitionName'];
            
            if (!$applicationStateMachine->can($application, $transitionName)) {
                $event->getForm()->addError(new FormError('Transition status not allowed'));
                return;
            }

            $applicationStateMachine->apply($application, $transitionName);
            
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Application::class,
        ]);
    }
}
