<?php

namespace Laravel\Nova\Menu;

use Illuminate\Support\Traits\Macroable;
use Laravel\Nova\AuthorizedToSee;
use Laravel\Nova\Fields\Collapsable;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Makeable;
use Laravel\Nova\WithIcon;

/**
 * @method static static make(string $name, array $items = [])
 */
class MenuGroup implements \JsonSerializable
{
    use AuthorizedToSee;
    use Makeable;
    use Macroable;
    use Collapsable;
    use WithIcon;

    /**
     * The menu's component.
     *
     * @var string
     */
    public $component = 'menu-group';

    /**
     * The menu's name.
     *
     * @var string
     */
    public $name;

    /**
     * The main group name.
     *
     * @var string
     */
    public $mainGroup = 'main';

    /**
     * The menu's items.
     *
     * @var \Laravel\Nova\Menu\MenuCollection
     */
    public $items;

    /**
     * Construct a new Menu Group instance.
     *
     * @param  string  $name
     * @param  array  $items
     */
    public function __construct($name, $items = [], $icon = 'collection')
    {
        $this->name = $name;
        $this->collapsedByDefault = true;
        $this->items = new MenuCollection($items);
        $this->withIcon($icon);
    }

    /**
     * Get the menu's unique key.
     *
     * @return string
     */
    public function key()
    {
        return md5($this->name.$this->items->reduce(function ($carry, $item) {
            return $carry.'-'.$item->name;
        }, ''));
    }

    /**
     * Set icon to the menu.
     *
     * @param  string  $icon
     * @return $this
     */
    public function icon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Set the main group name
     *
     * @return $this
     */
    public function mainGroup($name)
    {
        $this->mainGroup = $name;

        return $this;
    }

    /**
     * Prepare the menu for JSON serialization.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $request = app(NovaRequest::class);

        return [
            'component' => $this->component,
            'name' => $this->name,
            'mainGroup' => $this->mainGroup,
            'icon' => $this->icon,
            'items' => $this->items->authorized($request)->withoutEmptyItems()->all(),
            'collapsable' => $this->collapsable,
            'collapsedByDefault' => $this->collapsedByDefault,
            'key' => $this->key(),
        ];
    }
}
