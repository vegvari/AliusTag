<?php

namespace Alius\Tag;

use PHPUnit_Framework_TestCase;

class TagTest extends PHPUnit_Framework_TestCase
{
    public function testTag()
    {
        $tag = new Tag('div');
        $this->assertSame('div', $tag->getTag());

        $tag->setTag('img');
        $this->assertSame('img', $tag->getTag());

        // test chainable
        $this->assertSame($tag, $tag->setTag('span'));
    }

    public function testDefaultSingletons()
    {
        $singletons = ['area', 'base', 'br', 'col', 'command', 'embed', 'hr',
            'img', 'input', 'link', 'meta', 'param', 'source', 'wbr', ];

        foreach ($singletons as $value) {
            $tag = new Tag($value);
            $this->assertSame(true, $tag->isSingleton());
        }
    }

    public function testSingleton()
    {
        $tag = new Tag('div');
        $this->assertSame(false, $tag->isSingleton());

        $tag->singleton();
        $this->assertSame(true, $tag->isSingleton());

        // test chainable
        $this->assertSame($tag, $tag->singleton());
    }

    public function testSetName()
    {
        $tag = Tag::img('url');
        $this->assertSame($tag, $tag->setName('foo'));
        $this->assertSame('foo', $tag->getName());
        $this->assertSame('<img src="url" alt="" />', (string) $tag);
    }

    public function testFindFirst()
    {
        $first = Tag::img('url');
        $second = Tag::img('url')->id('bar');
        $third = Tag::img('url')->setName('foobar');
        $tag = Tag::div()->add($first)->add($second)->add($third);

        $this->assertSame($first, $tag->findFirst('img'));
        $this->assertSame($second, $tag->findFirst('bar'));
        $this->assertSame($third, $tag->findFirst('foobar'));
    }

    public function testChangeFirst()
    {
        $img = Tag::img('url');
        $tag = Tag::div($img);

        $tag->changeFirst('img', function (Tag $tag) {
            return $tag->alt('foobar');
        });

        $this->assertSame('foobar', $img->getAttr('alt'));
        $this->assertSame($tag, $tag->changeFirst('img', function (Tag $tag) {
            return $tag;
        }));
        $this->assertSame('<div><img src="url" alt="foobar" /></div>', $tag->render());

        // nothing happens
        $this->assertSame(null, $tag->findFirst('foobar'));
        $this->assertSame($tag, $tag->changeFirst('foobar', function (Tag $tag) {
            return $tag;
        }));
    }

    public function testAttr()
    {
        $tag = new Tag('div');

        // initial state
        $this->assertSame(null, $tag->getAttr('foo'));
        $this->assertSame(false, $tag->hasAttr('foo'));
        $this->assertSame('', $tag->renderAttr());

        // delete
        $tag->deleteAttr('foo');

        // set foo with null
        $tag->attr('foo');
        $this->assertSame(null, $tag->getAttr('foo'));
        $this->assertSame(true, $tag->hasAttr('foo'));
        $this->assertSame('foo=""', $tag->renderAttr());

        // set foo with empty string
        $tag->attr('foo', '');
        $this->assertSame('', $tag->getAttr('foo'));
        $this->assertSame(true, $tag->hasAttr('foo'));
        $this->assertSame('foo=""', $tag->renderAttr());

        // delete
        $tag->deleteAttr('foo');

        // initial state
        $this->assertSame(null, $tag->getAttr('foo'));
        $this->assertSame(false, $tag->hasAttr('foo'));
        $this->assertSame('', $tag->renderAttr());

        // testing types
        $tag->attr('foo', true);
        $this->assertSame(true, $tag->getAttr('foo'));

        $tag->attr('foo', 1);
        $this->assertSame(1, $tag->getAttr('foo'));

        $tag->attr('foo', 1.1);
        $this->assertSame(1.1, $tag->getAttr('foo'));

        $tag->attr('foo', '1');
        $this->assertSame('1', $tag->getAttr('foo'));

        // testing chainable
        $this->assertSame($tag, $tag->attr('foo'));
        $this->assertSame($tag, $tag->deleteAttr('foo'));

        // converting html entities
        $tag = new Tag('div');
        $tag->attr('foo', '"" \'\' <script></script>');
        $this->assertSame('"" \'\' <script></script>', $tag->getAttr('foo'));
        $this->assertSame('foo="&quot;&quot; \'\' &lt;script&gt;&lt;/script&gt;"', $tag->renderAttr());
    }

    public function testContent()
    {
        $tag = new Tag('div');

        // initial state
        $this->assertSame(false, $tag->hasContent());
        $this->assertSame('', $tag->renderContent());

        // delete
        $tag->deleteContent();

        // add null
        $tag->add(null);
        $this->assertSame(false, $tag->hasContent());
        $this->assertSame('', $tag->renderContent());

        // add empty string
        $tag->add('');
        $this->assertSame(false, $tag->hasContent());
        $this->assertSame('', $tag->renderContent());

        // add another instance
        $another_tag = new Tag('div');
        $tag->add($another_tag);
        $this->assertSame(true, $tag->hasContent());
        $this->assertSame($another_tag->render(), $tag->renderContent());

        // replace existing content
        $tag->setContent('foo');
        $this->assertSame(true, $tag->hasContent());
        $this->assertSame('foo', $tag->renderContent());

        // delete
        $tag->deleteContent();

        // initial state
        $this->assertSame(false, $tag->hasContent());
        $this->assertSame('', $tag->renderContent());

        // testing chainable
        $this->assertSame($tag, $tag->add('foo'));
        $this->assertSame($tag, $tag->setContent('foo'));
        $this->assertSame($tag, $tag->deleteContent());

        // converting html entities
        $tag = new Tag('div');
        $tag->add('"" \'\' <script></script>');
        $this->assertSame('&quot;&quot; &#039;&#039; &lt;script&gt;&lt;/script&gt;', $tag->renderContent());
    }

    public function testClass()
    {
        $tag = new Tag('div');

        // initial state
        $this->assertSame([], $tag->getClass());
        $this->assertSame(false, $tag->hasAttr('class'));
        $this->assertSame('', $tag->renderAttr());

        // delete
        $tag->deleteClass('foo');

        // add null
        $tag->addClass(null);
        $this->assertSame([], $tag->getClass());
        $this->assertSame(false, $tag->hasClass(null));
        $this->assertSame(true, $tag->hasAttr('class'));
        $this->assertSame('class=""', $tag->renderAttr());

        // add empty string
        $tag->addClass('');
        $this->assertSame([], $tag->getClass());
        $this->assertSame(false, $tag->hasClass(''));
        $this->assertSame(true, $tag->hasAttr('class'));
        $this->assertSame('class=""', $tag->renderAttr());

        // add foo
        $tag->addClass('foo');
        $this->assertSame(['foo'], $tag->getClass());
        $this->assertSame(true, $tag->hasClass('foo'));
        $this->assertSame('class="foo"', $tag->renderAttr());

        // add bar
        $tag->addClass('bar');
        $this->assertSame(['foo', 'bar'], $tag->getClass());
        $this->assertSame(true, $tag->hasClass('bar'));
        $this->assertSame('class="foo bar"', $tag->renderAttr());

        // add classes in string
        $tag->addClass('          apple       pear   ');
        $this->assertSame(['foo', 'bar', 'apple', 'pear'], $tag->getClass());
        $this->assertSame('class="foo bar apple pear"', $tag->renderAttr());

        // replace with classes in array
        $tag->setClass(['   black    ', '    white  ']);
        $this->assertSame(['black', 'white'], $tag->getClass());
        $this->assertSame('class="black white"', $tag->renderAttr());

        // delete one class (array sort!)
        $tag->deleteClass('black');
        $this->assertSame(['white'], $tag->getClass());
        $this->assertSame('class="white"', $tag->renderAttr());

        // delete last class (remove the attribute)
        $tag->deleteClass('white');

        // initial state
        $this->assertSame([], $tag->getClass());
        $this->assertSame(false, $tag->hasAttr('class'));
        $this->assertSame('', $tag->renderAttr());

        // testing types
        $tag->setClass(true);
        $this->assertSame(['1'], $tag->getClass());

        $tag->setClass(1);
        $this->assertSame(['1'], $tag->getClass());

        $tag->setClass(1.1);
        $this->assertSame(['1.1'], $tag->getClass());

        $tag->setClass('1');
        $this->assertSame(['1'], $tag->getClass());

        // test chainable
        $this->assertSame($tag, $tag->addClass('foo'));
        $this->assertSame($tag, $tag->setClass('bar'));
        $this->assertSame($tag, $tag->deleteClass('bar'));

        // test hasClass regexp
        $tag = Tag::div()->class('test_foo test_bar12');
        $this->assertSame(false, $tag->hasClass('test'));
        $this->assertSame(true, $tag->hasClass('test.+'));
        $this->assertSame(false, $tag->hasClass('foo'));
        $this->assertSame(true, $tag->hasClass('.+foo'));
        $this->assertSame(false, $tag->hasClass('.+bar'));
        $this->assertSame(true, $tag->hasClass('.+bar[0-9]+'));
    }

    public function testData()
    {
        $tag = new Tag('div');

        // initial state
        $this->assertSame(null, $tag->getData('foo'));
        $this->assertSame(false, $tag->hasData('foo'));
        $this->assertSame('', $tag->renderAttr());

        // delete
        $tag->deleteData('foo');

        // set null
        $tag->data('foo', null);
        $this->assertSame(null, $tag->getData('foo'));
        $this->assertSame(true, $tag->hasData('foo'));
        $this->assertSame('data-foo=""', $tag->renderAttr());

        // set empty string
        $tag->data('foo', '');
        $this->assertSame('', $tag->getData('foo'));
        $this->assertSame(true, $tag->hasData('foo'));
        $this->assertSame('data-foo=""', $tag->renderAttr());

        // set bar
        $tag->data('foo', 'bar');
        $this->assertSame('bar', $tag->getData('foo'));
        $this->assertSame(true, $tag->hasData('foo'));
        $this->assertSame('data-foo="bar"', $tag->renderAttr());

        // delete
        $tag->deleteData('foo');

        // initial state
        $this->assertSame(null, $tag->getData('foo'));
        $this->assertSame(false, $tag->hasData('foo'));
        $this->assertSame('', $tag->renderAttr());

        // testing types
        $tag->data('foo', true);
        $this->assertSame(true, $tag->getData('foo'));

        $tag->data('foo', 1);
        $this->assertSame(1, $tag->getData('foo'));

        $tag->data('foo', 1.1);
        $this->assertSame(1.1, $tag->getData('foo'));

        $tag->data('foo', '1');
        $this->assertSame('1', $tag->getData('foo'));

        // testing chainable
        $this->assertSame($tag, $tag->data('foo'));
        $this->assertSame($tag, $tag->deleteData('foo'));
    }

    public function testRender()
    {
        // singleton
        $tag = new Tag('img');
        $this->assertSame('<img />', $tag->render());

        $tag->attr('foo', 'bar');
        $this->assertSame('<img foo="bar" />', $tag->render());

        $tag->add('foo bar');
        $this->assertSame('<img foo="bar" />foo bar', $tag->render());

        $tag->class('foo bar');
        $this->assertSame('<img foo="bar" class="foo bar" />foo bar', $tag->render());

        $tag->data('foo', 'bar');
        $this->assertSame('<img foo="bar" class="foo bar" data-foo="bar" />foo bar', $tag->render());

        // test cast to string
        $tag = new Tag('div');
        $this->assertSame('<div></div>', (string) $tag);

        $tag->attr('foo', 'bar');
        $this->assertSame('<div foo="bar"></div>', (string) $tag);

        $tag->add('foo bar');
        $this->assertSame('<div foo="bar">foo bar</div>', (string) $tag);

        $tag->class('foo bar');
        $this->assertSame('<div foo="bar" class="foo bar">foo bar</div>', (string) $tag);

        $tag->data('foo', 'bar');
        $this->assertSame('<div foo="bar" class="foo bar" data-foo="bar">foo bar</div>', (string) $tag);
    }

    public function testCall()
    {
        $tag = new Tag('div');

        $tag->foo('bar');
        $this->assertSame('bar', $tag->getAttr('foo'));

        $tag->class('foo');
        $this->assertSame(['foo'], $tag->getClass());

        $tag->class('bar');
        $this->assertSame(['bar'], $tag->getClass());
    }

    public function testCallStatic()
    {
        $tag = Tag::stuff();
        $this->assertSame('<stuff></stuff>', $tag->render());
    }

    public function testFactories()
    {
        $instance = Tag::div();
        $this->assertSame('<div></div>', $instance->render());

        $instance = Tag::div('test');
        $this->assertSame('<div>test</div>', $instance->render());

        $instance = Tag::span();
        $this->assertSame('<span></span>', $instance->render());

        $instance = Tag::span('test');
        $this->assertSame('<span>test</span>', $instance->render());

        $instance = Tag::a('url', 'text');
        $this->assertSame('<a href="url">text</a>', $instance->render());

        $instance = Tag::img('url');
        $this->assertSame('<img src="url" alt="" />', $instance->render());

        $instance = Tag::form('url');
        $this->assertSame('<form action="url" method="post"></form>', $instance->render());

        $instance = Tag::form('url', 'post', 'token');
        $this->assertSame('<form action="url" method="post"><input type="hidden" name="_token" value="token" /></form>', $instance->render());

        $instance = Tag::form('url', 'get');
        $this->assertSame('<form action="url" method="get"></form>', $instance->render());

        $instance = Tag::form('url', 'put');
        $this->assertSame('<form action="url" method="post"><input type="hidden" name="_method" value="put" /></form>', $instance->render());

        $instance = Tag::form('url', 'put', 'token');
        $this->assertSame('<form action="url" method="post"><input type="hidden" name="_method" value="put" /><input type="hidden" name="_token" value="token" /></form>', $instance->render());

        $instance = Tag::label();
        $this->assertSame('<label></label>', $instance->render());

        $instance = Tag::labelFor('id');
        $this->assertSame('<label for="id"></label>', $instance->render());

        $instance = Tag::labelFor(Tag::img('url')->id('stuff'), 'test');
        $this->assertSame('<label for="stuff">test</label>', $instance->render());

        $instance = Tag::labelFor(Tag::img('url'), 'test');
        $this->assertSame('<label>test</label>', $instance->render());

        $instance = Tag::caption('stuff');
        $this->assertSame('<caption>stuff</caption>', $instance->render());

        $instance = Tag::input('text', 'stuff');
        $this->assertSame('<input type="text" name="stuff" />', $instance->render());

        $instance = Tag::input('text', 'stuff', 'default');
        $this->assertSame('<input type="text" name="stuff" value="default" />', $instance->render());

        $instance = Tag::checkbox('test', 'stuff');
        $this->assertSame('<input type="checkbox" name="test" value="stuff" />', $instance->render());

        $instance = Tag::checkbox('test', 'stuff', true);
        $this->assertSame('<input type="checkbox" name="test" value="stuff" checked="checked" />', $instance->render());

        $instance = Tag::radio('test', 'stuff');
        $this->assertSame('<input type="radio" name="test" value="stuff" />', $instance->render());

        $instance = Tag::radio('test', 'stuff', true);
        $this->assertSame('<input type="radio" name="test" value="stuff" checked="checked" />', $instance->render());

        $instance = Tag::text('test');
        $this->assertSame('<input type="text" name="test" />', $instance->render());

        $instance = Tag::text('test', 'stuff');
        $this->assertSame('<input type="text" name="test" value="stuff" />', $instance->render());

        $instance = Tag::password('test');
        $this->assertSame('<input type="password" name="test" />', $instance->render());

        $instance = Tag::hidden('test', 'stuff');
        $this->assertSame('<input type="hidden" name="test" value="stuff" />', $instance->render());

        $instance = Tag::select('test');
        $this->assertSame('<select name="test"></select>', $instance->render());

        $instance = Tag::select('test', [Tag::option('test', 'stuff'), Tag::option('test2', 'stuff2')]);
        $this->assertSame('<select name="test"><option value="test">stuff</option><option value="test2">stuff2</option></select>', $instance->render());

        $instance = Tag::option('test', 'stuff');
        $this->assertSame('<option value="test">stuff</option>', $instance->render());

        $instance = Tag::option('test', 'stuff', true);
        $this->assertSame('<option value="test" selected="selected">stuff</option>', $instance->render());

        $instance = Tag::textarea('test');
        $this->assertSame('<textarea name="test"></textarea>', $instance->render());

        $instance = Tag::textarea('test', 'stuff');
        $this->assertSame('<textarea name="test">stuff</textarea>', $instance->render());
    }

    public function testHelloWorld()
    {
        $tag = Tag::html()->lang('en')
            ->add(Tag::head()
                ->add(Tag::title()->add('HTML5'))
                ->add(Tag::meta()->charset('utf-8'))
                ->add(Tag::meta()->author('Romeo Vegvari')))
            ->add(Tag::body()
                ->add(Tag::div('Hello World!')));

        $this->assertSame('<html lang="en"><head><title>HTML5</title><meta charset="utf-8" /><meta author="Romeo Vegvari" /></head><body><div>Hello World!</div></body></html>', $tag->render());
    }
}
