<?php

namespace Jeffreyvr\DropBlockEditor\Components;

use App\Models\Form;
use App\Models\FormField;
use Jeffreyvr\DropBlockEditor\Parsers\Parse;
use Livewire\Component;
use Mary\Traits\Toast;

class ExampleButton extends Component
{
    use Toast;
    public $properties;

    public Form $form;

    protected $listeners = [
        'editorIsUpdated' => 'editorIsUpdated',
    ];

    public function editorIsUpdated($properties)
    {
        $this->properties = $properties;
    }

    public function save()
    {
        $this->form = Form::find($this->properties['form']['id']);
        $this->authorize('update', $this->form);
        $activeBlocks = $this->properties['activeBlocks'];
        // dd($this->properties);
        // Example of getting a json string of the active blocks.
        // $activeBlocks = collect($this->properties['activeBlocks'])
        //     ->toJson();

        // dd($activeBlocks[0]);
        // If you want to generate the output, you can do:
        $output = Parse::execute([
            'activeBlocks' => $activeBlocks,
            'base' => $this->properties['base'],
            'context' => 'rendered',
            'parsers' => $this->properties['parsers'],
        ]);
        
        // dd($activeBlocks[0]);
        // dd( json_decode($this->form->formFields->first()->data, true));

        $this->form->formFields()->delete();
        foreach ($activeBlocks as $key => $activeBlock) {
            $class_name = $activeBlock['class'];
            $field = new FormField([
                'uuid' => $activeBlock['data']['uuid'], 
                'field_class' => $class_name, 
                'data' => json_encode($activeBlock)
            ]);
            $this->form->formFields()->save($field);
        }

        $this->success('Form updated with success', position: 'toast-bottom');

        // dd($this->form->formFields);

    }

    public function render()
    {
        return <<<'blade'
            <div>
                <button wire:click="save" wire:confirm="Are you sure?" class="bg-blue-200 text-blue-900 rounded px-3 py-1 text-sm">Save</button>
            </div>
        blade;
    }
}
