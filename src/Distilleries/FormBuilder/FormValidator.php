<?php
/**
 * Created by PhpStorm.
 * User: mfrancois
 * Date: 11/02/2015
 * Time: 10:40 AM
 */

namespace Distilleries\FormBuilder;
use \Validator, \Input, \Redirect;

class FormValidator extends FormView {

    public static $rules = [];
    public static $rules_update = null;

    // ------------------------------------------------------------------------------------------------

    protected $hasError = false;
    protected $validation = null;
    protected $formOptions = [
        'method' => 'POST',
        'url'    => null,
    ];

    // ------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------

    public function add($name, $type = 'text', array $options = [], $modify = false, $noOveride = false)
    {

        $defaultClass = $this->formHelper->getConfig('defaults.field_class') . ' ';
        if (empty($options['attr']['class']))
        {
            $options['attr']['class'] = '';
        }

        if (empty($noOveride))
        {
            $options['attr']['class'] = $defaultClass . ' ' . $options['attr']['class'] . ' ';
        }

        if (!empty($options) and isset($options['validation']))
        {

            $options['attr']['class'] .= ' validate[' . $options['validation'] . ']' . ' ';
            unset($options['validation']);
        }

        if ($type == 'choice' and !isset($options['selected']))
        {
            if (isset($this->model->{$name}))
            {
                $options['selected'] = $this->model->{$name};
            }
        }

        if (!isset($options['noInEditView']))
        {
            $options['noInEditView'] = false;
        }


        if (!empty($this->formOptions) and !empty($this->formOptions['do_not_display_' . $name]) and $this->formOptions['do_not_display_' . $name] === true)
        {
            $type = 'hidden';

            if (!empty($options) and !empty($options['selected']))
            {
                $options['default_value'] = $options['selected'];
            }

        }

        return parent::add($name, $type, $options, $modify);
    }

    // ------------------------------------------------------------------------------------------------

    public function validateAndRedirectBack()
    {
        return Redirect::back()->withErrors($this->validate())->withInput(Input::all());

    }

    // ------------------------------------------------------------------------------------------------

    public function validate()
    {
        if ($this->validation == null)
        {

            $fields = $this->getFields();

            foreach ($fields as $field)
            {
                if ($field->getType() == 'form')
                {

                    $validation = Validator::make(Input::get($field->getName()), $field->getClass()->getRules());

                    if ($validation->fails())
                    {
                        $this->hasError   = true;
                        $this->validation = $validation;

                        return $this->validation;
                    }
                }
            }

            $validation = Validator::make(Input::all(), $this->getRules());

            if ($validation->fails())
            {
                $this->hasError = true;
            }

            $this->validation = $validation;
        }

        return $this->validation;
    }

    // ------------------------------------------------------------------------------------------------

    public function hasError()
    {

        if ($this->validation == null)
        {
            $this->validate();
        }

        return $this->hasError;
    }


    // ------------------------------------------------------------------------------------------------

    public function addDefaultActions()
    {
        $this->add('submit', 'submit',
            [
                'label' => _('Save'),
                'attr'  => [
                    'class' => 'btn green'
                ],
            ], false, true)
            ->add('back', 'button',
                [
                    'label' => _('Back'),
                    'attr'  => [
                        'class'   => 'btn default',
                        'onclick' => 'window.history.back()'
                    ],
                ], false, true);

    }

    // ------------------------------------------------------------------------------------------------

    protected function getRules()
    {
        $key = !empty($this->model) ? Input::get($this->model->getKeyName()) : null;

        return ($this->getUpdateRules() == null or empty($key)) ? $this->getGeneralRules() : $this->getUpdateRules();
    }

    // ------------------------------------------------------------------------------------------------

    protected function getGeneralRules()
    {

        return static::$rules;
    }

    // ------------------------------------------------------------------------------------------------

    protected function getUpdateRules()
    {

        return static::$rules_update;
    }


    // ------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------

}