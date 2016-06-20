[![Build Status](https://travis-ci.org/vegvari/AliusTag.svg?branch=master)](https://travis-ci.org/vegvari/AliusTag)

# Alius Tag

It's just a simple class to create tags.

### Basic usage

```
$img = new Tag('img');
print $img; // <img />

$img->src('url')
print $img; // <img src="url" />

$img->alt();
print $img; // <img src="url" alt="" />

$img->alt('Cute cat')
print $img; // <img src="url" alt="Cute cat" />

$img->class('cute');
print $img; // <img src="url" alt="Cute cat" class="cute" />

$img->class('dog');
print $img; // <img src="url" alt="Cute cat" class="dog" />

$img->addClass('cute');
print $img; // <img src="url" alt="Cute cat" class="dog cute" />

$div = new Tag('div');
$div->add($img);
print $div; // <div><img src="url" alt="Cute cat" class="dog cute" /></div>

$div->data('ng-class', 'test');
print $div; // <div data-ng-class="test"><img src="url" alt="Cute cat" class="dog cute" /></div>
```

Use the render method or cast the class to string to get the html.

## Methods

### Attributes

#### object attr ( string $name, mixed $value = null )

Change the attribute. Chainable.

```
$tag = new Tag('div');
$tag->attr('foo'); // <div foo=""></div>>
$tag->attr('foo', ''); // <div foo=""></div>
$tag->attr('foo', 'bar'); // <div foo="bar"></div>
```

Preserves the original php type:

```
$tag->attr('foo', true); // <div foo="1"></div>
$tag->getAttr('foo'); // true
```

Html entities and double quotes are converted (only when rendered):

```
$tag->attr('foo', '"foo" \'bar\' <script></script>'); // <div foo="&quot;foo&quot; 'bar' &lt;script&gt;&lt;/script&gt;"></div>
$tag->getAttr('foo'); // "foo" 'bar' <script></script>
```

#### mixed getAttr ( string $name )

Get the attribute value.

#### bool hasAttr ( string $name )

True if the attribute exists. Even if it's empty.

#### object deleteAttr ( string $name )

Delete the attribute. Chainable.

#### string renderAttr ( string $name )

Get the attributes in string.

### Content

#### object add ( mixed $value )

Add content. Chainable. The value is converted to string, null or empty string is skipped.

```
$tag = new Tag('div');
$tag->add('foo')->add('bar'); // <div>foobar</div>
```

#### object setContent ( mixed $value )

Replaces the content. Chainable.

#### bool hasContent

True if there is some content.

#### object deleteContent

Delete the content. Chainable.

#### string renderContent

Get the content string.

### Class

Helper methods for classes.

#### object addClass ( $values )

Add class(es). Chainable. Null and empty string skipped. You can pass an array or string. Don't worry about whitespaces.

```
$tag = new Tag('div');
$tag->addClass('foo')-addClass('bar'); // <div class="foo bar"></div>
$tag->addClass([' a ', ' b c ']); // <div class="foo bar a b c"></div>
```

#### object setClass ( $values )

Replace existing classes. Chainable. You can use it with the class pseudo method via __call:

```
$tag = Tag('img');
$tag->class('test); // <img class="test" />
```

#### array getClass ()

Get the classes.

#### bool hasClass ( $value )

True when the class is set.

#### object deleteClass ( $values )

Delete one class. Chainable. Deletes the class attribute when you delete the last class.

### Data

Helper methods for data attributes.

#### object data ( $name, $value = null )

Set the data attribute. Chainable.

```
$tag = new Tag('div');
$tag->data('foo', 'bar'); // <div data-foo="bar"></div>
```

#### mixed getData ( $name )

Get the data value. Null if the data is not set.

#### bool hasData ( $name )

True if there is a data attribute with this name, even if it's empty.

#### object deleteData ( $name )

Delete the attribute.

### Other

Non-existing methods are treated as attributes using the __call method:

```
$tag = new Tag('img');
$tag->foo('bar')->bar('foo'); // <img foo="bar" bar="foo" />
```

You can use this class statically:

```
$tag = Tag::anything(); // <anything></anything>
```

There is a general factory:

##### object make ( string $tag )

And there are some useful helper factories:

#### object form ( string $action, $method = 'post', $token = null )

If the form isn't get or post we change the method to post and add a _method hidden tag:

```
$tag = Tag::form('url', 'delete');
```

```
<form action="url" method="post">
    <input type="hidden" name="_method" value="delete" />
</form>
```

If you set the token we place a _token hidden tag:

```
$tag = Tag::form('url', 'post', 'foo bar');
```

```
<form action="url" method="post">
    <input type="hidden" name="_token" value="foo bar" />
</form>
```

#### object labelFor ( mixed $for, string $text = null )

You can add string or even an other tag:

```
$tag = Tag::labelFor(Tag::foo()->id('bar'), 'foobar'); // <label for"bar">foobar</label>
```

#### object select ( string $name, array $options = [] )

Use it with option tags:

```
$tag = Tag::select('foo', [
    Tag::option('one', 1),
    Tag::option('two', 2),
    Tag::option('three', 3),
]);
```

```
<select name="foo">
    <option value="1">one</option>
    <option value="2">two</option>
    <option value="3">three</option>
</select>
```

##### object div ( mixed $content = null )

##### object span ( mixed $content = null )

##### object a ( string $href, mixed $content = null )

##### object img ( string $src )

##### object caption ( mixed $text = null )

##### object input ( string $type, string $name, string $value = null )

##### object checkbox ( string $name, string $value = null, $checked = false )

##### object radio ( string $name, string $value = null, $checked = false )

##### object text ( string $name, string $value = null )

##### object password ( string $name )

##### object hidden ( string $name, string $value )

##### object option ( string $text, string $value = null, $selected = false )

##### object textarea ( string $name, string $value = null )

### Hello World

```
print Tag::html()->lang('en')
    ->add(Tag::head()
        ->add(Tag::title()->add('HTML5'))
        ->add(Tag::meta()->charset('utf-8'))
        ->add(Tag::meta()->author('Romeo Vegvari'))
    )
    ->add(Tag::body()
        ->add(Tag::div('Hello World!'))
    );
```
