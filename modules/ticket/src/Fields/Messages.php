<?php

namespace App\Ticket\Fields;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Contracts\ListableField;
use Laravel\Nova\Panel;

class Messages extends Field implements ListableField
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'messages-field';

    /**
     * The field's resource class
     *
     * @var string
     */
    public $resourceClass;

    /**
     * The field's fields
     *
     * @var string
     */
    protected $fieldsCallback;

    /**
     * Can Send message
     *
     * @var boolean
     */
    protected $canSendMessage = false;

    /**
     * Create a new field.
     *
     * @param  string $name
     * @param  string|null $attribute
     * @param  mixed|null $resource
     * @return void
     */
    public function __construct($name)
    {
        parent::__construct($name, null);
        $this->onlyOnDetail();
    }


    /**
     * Make current field behaves as panel.
     *
     * @return \Laravel\Nova\Panel
     */
    public function asPanel() 
    {
        return Panel::make($this->name, [$this]);
    }

    /**
     * Resolve the field's value for display.
     *
     * @param  mixed  $resource
     * @param  string|null  $attribute
     * @return $this;
     */
    public function resolveForDisplay($resource, $attribute = null)
    {
        return $this;
    }

    /**
     * Specify the available options
     *
     * @param array $options
     * @return self
     */
    public function options($options)
    {

        if (is_callable($options)) {
            $options = $options();
        }

        return $this->withMeta(['options' => $options]);
    }

    /**
     * Can Send message
     *
     * @param $access
     * @return self
     */
    public function canSendMessage($access)
    {
        if (is_callable($access)) {
            $access = $access();
        }

        return $this->withMeta(['canSendMessage' => $access]);
    }
}