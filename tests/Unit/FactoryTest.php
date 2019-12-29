<?php

declare(strict_types=1);

namespace webignition\BasilDomIdentifierFactory\Tests\Unit;

use webignition\BasilDomIdentifier\DomIdentifier;
use webignition\BasilDomIdentifierFactory\Factory;

class FactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Factory
     */
    private $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = Factory::createFactory();
    }

    /**
     * @dataProvider attributeIdentifierDataProvider
     * @dataProvider cssSelectorIdentifierDataProvider
     * @dataProvider descendantIdentifierDataProvider
     * @dataProvider xpathExpressionIdentifierDataProvider
     */
    public function testCreateFromIdentifierStringSuccess(string $identifierString, DomIdentifier $expectedIdentifier)
    {
        $identifier = $this->factory->createFromIdentifierString($identifierString);

        $this->assertInstanceOf(DomIdentifier::class, $identifier);
        $this->assertEquals($expectedIdentifier, $identifier);
    }

    public function attributeIdentifierDataProvider(): array
    {
        return [
            'attribute identifier: css class selector, position: null' => [
                'identifierString' => '$".listed-item".attribute_name',
                'expectedIdentifier' => (new DomIdentifier('.listed-item'))
                    ->withAttributeName('attribute_name'),
            ],
            'attribute identifier: css class selector; position: 1' => [
                'identifierString' => '$".listed-item":1.attribute_name',
                'expectedIdentifier' => (new DomIdentifier('.listed-item', 1))
                    ->withAttributeName('attribute_name'),
            ],
            'attribute identifier: css class selector; position: -1' => [
                'identifierString' => '$".listed-item":-1.attribute_name',
                'expectedIdentifier' => (new DomIdentifier('.listed-item', -1))
                    ->withAttributeName('attribute_name'),
            ],
            'attribute identifier: css class selector; position: first' => [
                'identifierString' => '$".listed-item":first.attribute_name',
                'expectedIdentifier' => (new DomIdentifier('.listed-item', 1))
                    ->withAttributeName('attribute_name'),
            ],
            'attribute identifier: css class selector; position: last' => [
                'identifierString' => '$".listed-item":last.attribute_name',
                'expectedIdentifier' => (new DomIdentifier('.listed-item', -1))
                    ->withAttributeName('attribute_name'),
            ],
            'attribute identifier: xpath id selector' => [
                'identifierString' => '$"//*[@id=\"element-id\"]".attribute_name',
                'expectedIdentifier' => (new DomIdentifier('//*[@id="element-id"]'))
                    ->withAttributeName('attribute_name'),
            ],
            'attribute identifier: xpath attribute selector, position: null' => [
                'identifierString' => '$"//input[@type=\"submit\"]".attribute_name',
                'expectedIdentifier' => (new DomIdentifier('//input[@type="submit"]'))
                    ->withAttributeName('attribute_name'),
            ],
            'attribute identifier: xpath attribute selector; position: 1' => [
                'identifierString' => '$"//input[@type=\"submit\"]":1.attribute_name',
                'expectedIdentifier' => (new DomIdentifier('//input[@type="submit"]', 1))
                    ->withAttributeName('attribute_name'),
            ],
            'attribute identifier: xpath attribute selector; position: -1' => [
                'identifierString' => '$"//input[@type=\"submit\"]":-1.attribute_name',
                'expectedIdentifier' => (new DomIdentifier('//input[@type="submit"]', -1))
                    ->withAttributeName('attribute_name'),
            ],
            'attribute identifier: xpath attribute selector; position: first' => [
                'identifierString' => '$"//input[@type=\"submit\"]":first.attribute_name',
                'expectedIdentifier' => (new DomIdentifier('//input[@type="submit"]', 1))
                    ->withAttributeName('attribute_name'),
            ],
            'attribute identifier: xpath attribute selector; position: last' => [
                'identifierString' => '$"//input[@type=\"submit\"]":last.attribute_name',
                'expectedIdentifier' => (new DomIdentifier('//input[@type="submit"]', -1))
                    ->withAttributeName('attribute_name'),
            ],
        ];
    }

    public function cssSelectorIdentifierDataProvider(): array
    {
        return [
            'css id selector' => [
                'identifierString' => '$"#element-id"',
                'expectedIdentifier' => new DomIdentifier('#element-id'),
            ],
            'css class selector, position: null' => [
                'identifierString' => '$".listed-item"',
                'expectedIdentifier' => new DomIdentifier('.listed-item'),
            ],
            'css class selector; position: 1' => [
                'identifierString' => '$".listed-item":1',
                'expectedIdentifier' => new DomIdentifier('.listed-item', 1),
            ],
            'css class selector; position: 3' => [
                'identifierString' => '$".listed-item":3',
                'expectedIdentifier' => new DomIdentifier('.listed-item', 3),
            ],
            'css class selector; position: -1' => [
                'identifierString' => '$".listed-item":-1',
                'expectedIdentifier' => new DomIdentifier('.listed-item', -1),
            ],
            'css class selector; position: -3' => [
                'identifierString' => '$".listed-item":-3',
                'expectedIdentifier' => new DomIdentifier('.listed-item', -3),
            ],
            'css class selector; position: first' => [
                'identifierString' => '$".listed-item":first',
                'expectedIdentifier' => new DomIdentifier('.listed-item', 1),
            ],
            'css class selector; position: last' => [
                'identifierString' => '$".listed-item":last',
                'expectedIdentifier' => new DomIdentifier('.listed-item', -1),
            ],
        ];
    }

    public function descendantIdentifierDataProvider(): array
    {
        return [
            'direct descendant; css parent, css child' => [
                'identifierString' => '{{ $".parent" }} $".child"',
                'expectedIdentifier' => (new DomIdentifier('.child'))
                    ->withParentIdentifier(new DomIdentifier('.parent')),
            ],
            'direct descendant; css parent, xpath child' => [
                'identifierString' => '{{ $".parent" }} $"/child"',
                'expectedIdentifier' => (new DomIdentifier('/child'))
                    ->withParentIdentifier(new DomIdentifier('.parent')),
            ],
            'direct descendant; xpath parent, css child' => [
                'identifierString' => '{{ $"/parent" }} $".child"',
                'expectedIdentifier' => (new DomIdentifier('.child'))
                    ->withParentIdentifier(new DomIdentifier('/parent')),
            ],
            'direct descendant; xpath parent, xpath child' => [
                'identifierString' => '{{ $"/parent" }} $"/child"',
                'expectedIdentifier' => (new DomIdentifier('/child'))
                    ->withParentIdentifier(new DomIdentifier('/parent')),
            ],
            'indirect descendant' => [
                'string' => '{{ {{ $".inner-parent" }} $".inner-child" }} $".child"',
                'expectedIdentifier' => (new DomIdentifier('.child'))
                    ->withParentIdentifier(
                        (new DomIdentifier('.inner-child'))
                            ->withParentIdentifier(new DomIdentifier('.inner-parent'))
                    ),
            ],
            'indirectly indirect descendant' => [
                'string' => '{{ {{ {{ $".inner-inner-parent" }} $".inner-inner-child" }} $".inner-child" }} $".child"',
                'expectedIdentifier' => (new DomIdentifier('.child'))
                    ->withParentIdentifier(
                        (new DomIdentifier('.inner-child'))
                            ->withParentIdentifier(
                                (new DomIdentifier('.inner-inner-child'))
                                    ->withParentIdentifier(new DomIdentifier('.inner-inner-parent'))
                            )
                    ),
            ],
        ];
    }

    public function xpathExpressionIdentifierDataProvider(): array
    {
        return [
            'xpath id selector' => [
                'identifierString' => '$"//*[@id=\"element-id\"]"',
                'expectedIdentifier' => new DomIdentifier('//*[@id="element-id"]'),
            ],
            'xpath attribute selector, position: null' => [
                'identifierString' => '$"//input[@type=\"submit\"]"',
                'expectedIdentifier' => new DomIdentifier('//input[@type="submit"]'),
            ],
            'xpath attribute selector; position: 1' => [
                'identifierString' => '$"//input[@type=\"submit\"]":1',
                'expectedIdentifier' => new DomIdentifier('//input[@type="submit"]', 1),
            ],
            'xpath attribute selector; position: 3' => [
                'identifierString' => '$"//input[@type=\"submit\"]":3',
                'expectedIdentifier' => new DomIdentifier('//input[@type="submit"]', 3),
            ],
            'xpath attribute selector; position: -1' => [
                'identifierString' => '$"//input[@type=\"submit\"]":-1',
                'expectedIdentifier' => new DomIdentifier('//input[@type="submit"]', -1),
            ],
            'xpath attribute selector; position: -3' => [
                'identifierString' => '$"//input[@type=\"submit\"]":-3',
                'expectedIdentifier' => new DomIdentifier('//input[@type="submit"]', -3),
            ],
            'xpath attribute selector; position: first' => [
                'identifierString' => '$"//input[@type=\"submit\"]":first',
                'expectedIdentifier' => new DomIdentifier('//input[@type="submit"]', 1),
            ],
            'xpath attribute selector; position: last' => [
                'identifierString' => '$"//input[@type=\"submit\"]":last',
                'expectedIdentifier' => new DomIdentifier('//input[@type="submit"]', -1),
            ],
        ];
    }

    /**
     * @dataProvider unknownIdentifierStringDataProvider
     */
    public function testCreateFromIdentifierStringReturnsNull(string $identifierString)
    {
        $this->assertNull($this->factory->createFromIdentifierString($identifierString));
    }

    public function unknownIdentifierStringDataProvider(): array
    {
        return [
            'empty' => [
                'identifierString' => '',
            ],
            'element reference' => [
                'identifierString' => '$elements.element_name',
            ],
            'page element reference' => [
                'identifierString' => '$page_import_name.elements.element_name',
            ],
        ];
    }
}
