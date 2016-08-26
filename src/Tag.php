<?php

namespace Alius\Tag;

class Tag
{
    /**
     * @var array
     */
    protected static $singletons = ['area', 'base', 'br', 'col', 'command',
        'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'wbr', ];

    /**
     * @var string
     */
    protected $tag;

    /**
     * @var bool
     */
    protected $singleton = false;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var array
     */
    protected $content = [];

    protected $allowed_tags = [
        'strong', 'em', 'small', 'big', 'mark', 'del', 'strike', 'ins', 'sub', 'sup', 'code', 'abbr', 'cite', 'kbd', 'tt',
        'acronym', 'span',
    ];

    protected $allowed_singleton_tags = [
        'br', 'hr',
    ];

    /**
     * @param string $tag
     */
    public function __construct($tag)
    {
        $this->tag = $tag;
        $this->singleton = in_array($this->tag, static::$singletons);
    }

    /**
     * Cast to string
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
     * @param string $name
     * @param array  $args
     *
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
     * @param string $name
     * @param array  $args
     *
     * @return this
     */
    public static function __callStatic($name, array $args = [])
    {
        return new static($name);
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
     * @param string $name
     * @param mixed  $value
     *
     * @return this
     */
    public function attr($name, $value = null)
    {
        if ($name === 'class') {
            return $this->addClass($value);
        }

        $this->attributes[$name] = $value;
        return $this;
    }

    /**
     * Get attribute
     *
     * @param string $name
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
     * @param string $name
     *
     * @return bool
     */
    public function hasAttr($name)
    {
        return array_key_exists($name, $this->attributes);
    }

    /**
     * Delete attribute
     *
     * @param string $name
     *
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

            if (is_string($value)) {
                $value = htmlspecialchars($value, ENT_COMPAT);
            }

            $render[] = $key . '="' . $value . '"';
        }

        return implode(' ', $render);
    }

    /**
     * Add content
     *
     * @param mixed $text
     *
     * @return this
     */
    public function add($text)
    {
        if ($text !== null && $text !== '') {
            if (! $text instanceof self) {
                if (preg_match('/(?P<all>\<(?P<tag>' . implode('|', $this->allowed_tags) . ')(?P<attr>(\s+[a-z-_0-9]+\=\"[^\"<>]*\")*)\>(?P<content>.*?)\<\/\2>)/ui', $text, $matches, PREG_OFFSET_CAPTURE) === 1
                    || preg_match('/(?P<all>\<(?P<tag>' . implode('|', $this->allowed_singleton_tags) . ')(?P<attr>(\s+[a-z-_0-9]+\=\"[^\"<>]*\")*)(\s*\/)?\>)/ui', $text, $matches, PREG_OFFSET_CAPTURE) === 1
                ) {
                    $tag = new Tag($matches['tag'][0]);

                    if (preg_match_all('/\s+(?P<name>[a-z-_0-9]+)\=\"(?P<value>[^\"<>]*)\"/ui', $matches['attr'][0], $attr_matches) > 0) {
                        foreach ($attr_matches['name'] as $key => $name) {
                            $tag->attr($name, $attr_matches['value'][$key]);
                        }
                    }

                    if (isset($matches['content'])) {
                        $tag->setContent($matches['content'][0]);
                    }

                    $this->add(substr($text, 0, $matches['all'][1]));
                    $this->content[] = $tag;
                    $this->add(substr($text, $matches['all'][1] + strlen($matches['all'][0])));

                    return $this;
                }
            }

            $this->content[] = $text;
        }

        return $this;
    }

    /**
     * Replace the content
     *
     * @param mixed $value
     *
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
        return $this->content !== [];
    }

    /**
     * Delete the content
     *
     * @return this
     */
    public function deleteContent()
    {
        $this->content = [];
        return $this;
    }

    /**
     * Render the content
     *
     * @return string
     */
    public function renderContent()
    {
        $content = '';
        foreach ($this->content as $value) {
            if ($value instanceof static) {
                $content .= (string) $value;
                continue;
            }

            $content .= htmlspecialchars(htmlspecialchars_decode($value, ENT_QUOTES), ENT_QUOTES);
        }
        return $content;
    }

    /**
     * Add class
     *
     * @param mixed $values
     *
     * @return this
     */
    public function addClass($values)
    {
        if ($values === null || $values === '') {
            $this->attributes['class'] = [];
            return $this;
        }

        $values = is_array($values) ? $values : [$values];

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
     * @param string|array $values
     *
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
        if ($this->hasAttr('class')) {
            return $this->attributes['class'];
        }

        return [];
    }

    /**
     * Is the class set?
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasClass($name)
    {
        return in_array($name, $this->getClass());
    }

    /**
     * Delete class
     *
     * @param string $name
     *
     * @return this
     */
    public function deleteClass($name)
    {
        if (($index = array_search($name, $this->getClass())) !== false) {
            unset($this->attributes['class'][$index]);
            if (count($this->attributes['class']) === 0) {
                unset($this->attributes['class']);
                return $this;
            }

            sort($this->attributes['class']);
        }

        return $this;
    }

    /**
     * Replace data attribute
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return this
     */
    public function data($name, $value = null)
    {
        return $this->attr('data-' . $name, $value);
    }

    /**
     * Get data attribute
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getData($name)
    {
        return $this->getAttr('data-' . $name);
    }

    /**
     * Is this data attribute set?
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasData($name)
    {
        return $this->hasAttr('data-' . $name);
    }

    /**
     * Delete data attribute
     *
     * @param string $name
     *
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

        if ($this->isSingleton()) {
            $render[] = ' />';
            $render[] = $this->renderContent();
        }

        if (! $this->isSingleton()) {
            $render[] = '>';
            $render[] = $this->renderContent();
            $render[] = '</' . $this->tag . '>';
        }

        return implode('', $render);
    }

    /**
     * Simple factory
     *
     * @param string $tag
     *
     * @return this
     */
    public static function make($tag)
    {
        return new static($tag);
    }

    /**
     * Create a div
     *
     * @param mixed $content
     * @return this
     */
    public static function div($content = null)
    {
        return static::make('div')->add($content);
    }

    /**
     * Create a span
     *
     * @param mixed $content
     *
     * @return this
     */
    public static function span($content = null)
    {
        return static::make('span')->add($content);
    }

    /**
     * Create anchor
     *
     * @param string $href
     * @param mixed  $content
     * @return this
     */
    public static function a($href, $content)
    {
        return static::make('a')->href($href)->add($content);
    }

    /**
     * Create img
     *
     * @param string $src
     *
     * @return this
     */
    public static function img($src)
    {
        return static::make('img')->src($src)->alt();
    }

    /**
     * Create post form
     *
     * @param string      $action
     * @param string      $method
     * @param string|null $token
     *
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
     * @param mixed $text
     *
     * @return this
     */
    public static function label($text = null)
    {
        return static::make('label')->add($text);
    }

    /**
     * Create label for another tag
     *
     * @param mixed $for
     * @param mixed $text
     *
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
     * @param mixed $text
     *
     * @return this
     */
    public static function caption($text = null)
    {
        return static::make('caption')->add($text);
    }

    /**
     * Create input
     *
     * @param string $type
     * @param string $name
     * @param mixed  $value
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
     * @param string $name
     * @param mixed  $value
     * @param bool   $checked
     *
     * @return this
     */
    public static function checkbox($name, $value, $checked = null)
    {
        $instance = static::input('checkbox', $name, $value);

        if ($checked === true) {
            $instance->checked('checked');
        }

        return $instance;
    }

    /**
     * Create radio input
     *
     * @param string $name
     * @param mixed  $value
     * @param bool   $checked
     *
     * @return this
     */
    public static function radio($name, $value, $checked = null)
    {
        $instance = static::input('radio', $name, $value);

        if ($checked === true) {
            $instance->checked('checked');
        }

        return $instance;
    }

    /**
     * Create text input
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return this
     */
    public static function text($name, $value = null)
    {
        return static::input('text', $name, $value);
    }

    /**
     * Create password input
     *
     * @param string $name
     *
     * @return this
     */
    public static function password($name)
    {
        return static::input('password', $name);
    }

    /**
     * Create hidden input
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return this
     */
    public static function hidden($name, $value)
    {
        return static::input('hidden', $name, $value);
    }

    /**
     * Create select
     *
     * @param string $name
     * @param array  $options
     *
     * @return this
     */
    public static function select($name, array $options = [])
    {
        $instance = static::make('select')->name($name);

        foreach ($options as $value) {
            $instance->add($value);
        }

        return $instance;
    }

    /**
     * Create option
     *
     * @param mixed $text
     * @param mixed $value
     * @param bool  $selected
     *
     * @return this
     */
    public static function option($text, $value = null, $selected = null)
    {
        $instance = static::make('option')->add($text)->value($value);

        if ($selected === true) {
            $instance->selected('selected');
        }

        return $instance;
    }

    /**
     * Create textarea
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return this
     */
    public static function textarea($name, $value = null)
    {
        return static::make('textarea')->name($name)->add($value);
    }
}
