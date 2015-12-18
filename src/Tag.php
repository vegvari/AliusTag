<?php

namespace Alius\Tag;

class Tag
{
    /**
     * @var array
     */
    protected static $singletons = ['area', 'base', 'br', 'col', 'command',
        'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'wbr',
        '!DOCTYPE', ];

    /**
     * @var bool
     */
    protected $singleton = false;

    /**
     * @var string
     */
    protected $tag;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var string
     */
    protected $content = '';

    /**
     * @param string $tag
     */
    public function __construct($tag)
    {
        $this->setTag($tag);
        $this->singleton = in_array($this->tag, static::$singletons);
    }

    /**
     * Render when casting to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Add attributes
     *
     * @param  string $name
     * @param  array  $args
     * @return this
     */
    public function __call($name, array $args = [])
    {
        $value = null;
        if (array_key_exists(0, $args)) {
            $value = $args[0];
        }

        if ($name === 'class') {
            return $this->setClass($value);
        }

        return $this->attr($name, $value);
    }

    /**
     * Universal factory
     *
     * @param  string $name
     * @param  array  $args
     * @return this
     */
    public static function __callStatic($name, array $args = [])
    {
        return new static($name);
    }

    /**
     * Set the tag
     *
     * @param  string $tag
     * @return this
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
        return $this;
    }

    /**
     * Get the tag
     *
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Set singleton
     *
     * @return this
     */
    public function singleton()
    {
        $this->singleton = true;
        return $this;
    }

    /**
     * Is this a singleton?
     *
     * @return bool
     */
    public function isSingleton()
    {
        return $this->singleton;
    }

    /**
     * Replace attribute
     *
     * @param  string $name
     * @param  mixed  $value
     * @return this
     */
    public function attr($name, $value = null)
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    /**
     * Get attribute
     *
     * @param  string $name
     * @return mixed
     */
    public function getAttr($name)
    {
        if ($this->hasAttr($name)) {
            return $this->attributes[$name];
        }
    }

    /**
     * Is this attribute set?
     *
     * @param  string $name
     * @return bool
     */
    public function hasAttr($name)
    {
        return array_key_exists($name, $this->attributes);
    }

    /**
     * Delete attribute
     *
     * @param  string $name
     * @return this
     */
    public function deleteAttr($name)
    {
        if ($this->hasAttr($name)) {
            unset($this->attributes[$name]);
        }

        return $this;
    }

    /**
     * Render the attributes
     *
     * @return string
     */
    public function renderAttr()
    {
        $render = [];

        foreach ($this->attributes as $key => $value) {
            if (is_array($value)) {
                $value = implode(' ', $value);
            }

            if ($value === null) {
                $render[] = $key;
            } else {
                $render[] = $key . '="' . $value . '"';
            }
        }

        return implode(' ', $render);
    }

    /**
     * Add content
     *
     * @param  mixed $value
     * @return this
     */
    public function add($value)
    {
        $this->content .= (string) $value;
        return $this;
    }

    /**
     * Replace the content
     *
     * @param  mixed $value
     * @return this
     */
    public function setContent($value)
    {
        return $this->deleteContent()->add($value);
    }

    /**
     * Is there any content added?
     *
     * @return bool
     */
    public function hasContent()
    {
        return $this->content !== '';
    }

    /**
     * Delete the content
     *
     * @return this
     */
    public function deleteContent()
    {
        $this->content = '';
        return $this;
    }

    /**
     * Render the content
     *
     * @return string
     */
    public function renderContent()
    {
        return $this->content;
    }

    /**
     * Add class
     *
     * @param  mixed $values
     * @return this
     */
    public function addClass($values)
    {
        if ($values === null || $values === '') {
            return $this->attr('class', $values);
        }

        $values = is_array($values) ? $values : [$values];

        $classes = [];
        foreach ($values as $value) {
            $pieces = explode(' ', preg_replace('/\s+/', ' ', $value));

            foreach ($pieces as $piece) {
                if ($piece !== null && $piece !== '') {
                    $this->attributes['class'][] = (string) $piece;
                }
            }
        }

        return $this;
    }

    /**
     * Replace class
     *
     * @param  string|array $values
     * @return this
     */
    public function setClass($values)
    {
        return $this->deleteAttr('class')->addClass($values);
    }

    /**
     * Get class
     *
     * @return array
     */
    public function getClass()
    {
        if ($this->hasAttr('class') && is_array($this->attributes['class'])) {
            return $this->attributes['class'];
        }

        return [];
    }

    /**
     * Is the class set?
     *
     * @param  string $name
     * @return bool
     */
    public function hasClass($name)
    {
        return array_search($name, $this->getClass()) !== false;
    }

    /**
     * Delete class
     *
     * @param  string $name
     * @return this
     */
    public function deleteClass($name)
    {
        if (($index = array_search($name, $this->getClass())) !== false) {
            unset($this->attributes['class'][$index]);
            if (count($this->attributes['class']) === 0) {
                unset($this->attributes['class']);
            } else {
                sort($this->attributes['class']);
            }
        }

        return $this;
    }

    /**
     * Replace data attribute
     *
     * @param  string $name
     * @param  mixed  $value
     * @return this
     */
    public function data($name, $value = null)
    {
        return $this->attr('data-' . $name, $value);
    }

    /**
     * Get data attribute
     *
     * @param  string $name
     * @return mixed
     */
    public function getData($name)
    {
        return $this->getAttr('data-' . $name);
    }

    /**
     * Is this data attribute set?
     *
     * @param  string $name
     * @return bool
     */
    public function hasData($name)
    {
        return $this->hasAttr('data-' . $name);
    }

    /**
     * Delete data attribute
     *
     * @param  string $name
     * @return this
     */
    public function deleteData($name)
    {
        return $this->deleteAttr('data-' . $name);
    }

    /**
     * Render
     *
     * @return string
     */
    public function render()
    {
        $render[] = '<' . $this->tag;

        $attr = $this->renderAttr();
        if ($attr !== '') {
            $render[] = ' ' . $attr;
        }

        if (! $this->isSingleton()) {
            $render[] = '>';
            $render[] = $this->renderContent();
            $render[] = '</' . $this->tag;
        }

        $render[] = '>';

        if ($this->isSingleton()) {
            $render[] = $this->renderContent();
        }

        return implode('', $render);
    }

    /**
     * Simple factory
     *
     * @param  string $tag
     * @return this
     */
    public static function make($tag)
    {
        return new static($tag);
    }

    /**
     * Create a div
     *
     * @param  mixed $content
     * @return this
     */
    public static function div($content = null)
    {
        return static::make('div')->add($content);
    }

    /**
     * Create a span
     *
     * @param  mixed $content
     * @return this
     */
    public static function span($content = null)
    {
        return static::make('span')->add($content);
    }

    /**
     * Create anchor
     *
     * @param  string $href
     * @param  mixed  $content
     * @return this
     */
    public static function a($href, $content)
    {
        return static::make('a')->href($href)->add($content);
    }

    /**
     * Create img
     *
     * @param  string $src
     * @return this
     */
    public static function img($src)
    {
        return static::make('img')->src($src);
    }

    /**
     * Create post form
     *
     * @param  string $action
     * @param  string $method
     * @return this
     */
    public static function form($action, $method = 'post', $token = null)
    {
        $instance = static::make('form')->action($action);

        $method = strtolower($method);
        if ($method !== 'get' && $method !== 'post') {
            $instance->add(static::hidden('_method', $method));
            $method = 'post';
        }

        if ($token !== null) {
            $instance->add(static::hidden('_token', $token));
        }

        return $instance->method($method);
    }

    /**
     * Create label
     *
     * @param  mixed $text
     * @return this
     */
    public static function label($text = null)
    {
        return static::make('label')->add($text);
    }

    /**
     * Create label for another tag
     *
     * @param  mixed $for
     * @param  mixed $text
     * @return this
     */
    public static function labelFor($for, $text = null)
    {
        $tag = static::label($text);

        if ($for instanceof static) {
            $for = $for->getAttr('id');
        }

        if (! empty($for)) {
            $tag->for($for);
        }

        return $tag;
    }

    /**
     * Create caption
     *
     * @param  mixed $text
     * @return this
     */
    public static function caption($text = null)
    {
        return static::make('caption')->add($text);
    }

    /**
     * Create input
     *
     * @param  string $type
     * @param  string $name
     * @param  mixed  $value
     * @return this
     */
    public static function input($type, $name, $value = null)
    {
        $tag = static::make('input')->type($type)->name($name);

        if (! empty($value)) {
            $tag->value($value);
        }

        return $tag;
    }

    /**
     * Create checkox input
     *
     * @param  string $name
     * @param  mixed  $value
     * @param  bool   $checked
     * @return this
     */
    public static function checkbox($name, $value, $checked = false)
    {
        $instance = static::input('checkbox', $name, $value);

        if ($checked === true) {
            $instance->checked();
        }

        return $instance;
    }

    /**
     * Create radio input
     *
     * @param  string $name
     * @param  mixed  $value
     * @param  bool   $checked
     * @return this
     */
    public static function radio($name, $value, $checked = false)
    {
        $instance = static::input('radio', $name, $value);

        if ($checked === true) {
            $instance->checked();
        }

        return $instance;
    }

    /**
     * Create text input
     *
     * @param  string $name
     * @param  mixed  $value
     * @return this
     */
    public static function text($name, $value = null)
    {
        return static::input('text', $name, $value);
    }

    /**
     * Create password input
     *
     * @param  string $name
     * @return this
     */
    public static function password($name)
    {
        return static::input('password', $name);
    }

    /**
     * Create hidden input
     *
     * @param  string $name
     * @param  mixed  $value
     * @return this
     */
    public static function hidden($name, $value)
    {
        return static::input('hidden', $name, $value);
    }

    /**
     * Create select
     *
     * @param  string $name
     * @param  array  $options
     * @return this
     */
    public static function select($name, array $options = [])
    {
        $instance = static::make('select')->name($name);

        foreach ($options as $key => $value) {
            $instance->add($value);
        }

        return $instance;
    }

    /**
     * Create option
     *
     * @param  string $name
     * @param  mixed  $text
     * @param  bool   $selected
     * @return this
     */
    public static function option($value, $text, $selected = false)
    {
        $instance = static::make('option')->value($value)->add($text);

        if ($selected === true) {
            $instance->selected();
        }

        return $instance;
    }

    /**
     * Create textarea
     *
     * @param  string $name
     * @param  mixed  $value
     * @return this
     */
    public static function textarea($name, $value = null)
    {
        return static::make('textarea')->name($name)->add($value);
    }
}