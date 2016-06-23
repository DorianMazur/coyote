<?php

namespace Coyote\Http\Forms\User;

use Coyote\Repositories\Contracts\GroupRepositoryInterface as GroupRepository;
use Illuminate\Contracts\Auth\Access\Gate;

class AdminForm extends SettingsForm
{
    protected $group;
    protected $gate;

    public function __construct(GroupRepository $group, Gate $gate)
    {
        parent::__construct();

        $this->group = $group;
        $this->gate = $gate;
    }

    public function buildForm()
    {
        parent::buildForm();

        $this->add('skills', 'collection', [
            'label' => 'Umiejętności',
            'child_attr' => [
                'type' => 'child_form',
                'class' => SkillsForm::class,
                'value' => $this->data
            ]
        ]);

        $groups = $this->group->pluck('name', 'id')->toArray();

        $this->add('groups', 'choice', [
            'label' => 'Grupy użytkownika',
            'choices' => $groups,
            'property' => 'id'
        ]);
    }
}