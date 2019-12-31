<?php

declare(strict_types=1);

namespace webignition\BasilDomIdentifierFactory\Tests\Unit;

use webignition\BasilDomIdentifierFactory\Factory;
use webignition\DomElementIdentifier\AttributeIdentifier;
use webignition\DomElementIdentifier\ElementIdentifier;
use webignition\DomElementIdentifier\ElementIdentifierInterface;

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
    public function testCreateFromIdentifierStringSuccess(
        string $identifierString,
        ElementIdentifierInterface $expectedIdentifier
    ) {
        $identifier = $this->factory->createFromIdentifierString($identifierString);

        $this->assertEquals($expectedIdentifier, $identifier);
    }

    public function attributeIdentifierDataProvider(): array
    {
        return [
            'attribute identifier: css class selector, position: null' => [
                'identifierString' => '$".listed-item".attribute_name',
                'expectedIdentifier' => new AttributeIdentifier('.listed-item', 'attribute_name'),
            ],
            'attribute identifier: css class selector; position: 1' => [
                'identifierString' => '$".listed-item":1.attribute_name',
                'expectedIdentifier' => new AttributeIdentifier('.listed-item', 'attribute_name', 1),
            ],
            'attribute identifier: css class selector; position: -1' => [
                'identifierString' => '$".listed-item":-1.attribute_name',
                'expectedIdentifier' => new AttributeIdentifier('.listed-item', 'attribute_name', -1),
            ],
            'attribute identifier: css class selector; position: first' => [
                'identifierString' => '$".listed-item":first.attribute_name',
                'expectedIdentifier' => new AttributeIdentifier('.listed-item', 'attribute_name', 1),
            ],
            'attribute identifier: css class selector; position: last' => [
                'identifierString' => '$".listed-item":last.attribute_name',
                'expectedIdentifier' => new AttributeIdentifier('.listed-item', 'attribute_name', -1),
            ],
            'attribute identifier: xpath id selector' => [
                'identifierString' => '$"//*[@id=\"element-id\"]".attribute_name',
                'expectedIdentifier' => new AttributeIdentifier('//*[@id="element-id"]', 'attribute_name'),
            ],
            'attribute identifier: xpath attribute selector, position: null' => [
                'identifierString' => '$"//input[@type=\"submit\"]".attribute_name',
                'expectedIdentifier' => new AttributeIdentifier('//input[@type="submit"]', 'attribute_name'),
            ],
            'attribute identifier: xpath attribute selector; position: 1' => [
                'identifierString' => '$"//input[@type=\"submit\"]":1.attribute_name',
                'expectedIdentifier' => new AttributeIdentifier('//input[@type="submit"]', 'attribute_name', 1),
            ],
            'attribute identifier: xpath attribute selector; position: -1' => [
                'identifierString' => '$"//input[@type=\"submit\"]":-1.attribute_name',
                'expectedIdentifier' => new AttributeIdentifier('//input[@type="submit"]', 'attribute_name', -1),
            ],
            'attribute identifier: xpath attribute selector; position: first' => [
                'identifierString' => '$"//input[@type=\"submit\"]":first.attribute_name',
                'expectedIdentifier' => new AttributeIdentifier('//input[@type="submit"]', 'attribute_name', 1),
            ],
            'attribute identifier: xpath attribute selector; position: last' => [
                'identifierString' => '$"//input[@type=\"submit\"]":last.attribute_name',
                'expectedIdentifier' => new AttributeIdentifier('//input[@type="submit"]', 'attribute_name', -1),
            ],
        ];
    }

    public function cssSelectorIdentifierDataProvider(): array
    {
        return [
            'css id selector' => [
                'identifierString' => '$"#element-id"',
                'expectedIdentifier' => new ElementIdentifier('#element-id'),
            ],
            'css class selector, position: null' => [
                'identifierString' => '$".listed-item"',
                'expectedIdentifier' => new ElementIdentifier('.listed-item'),
            ],
            'css class selector; position: 1' => [
                'identifierString' => '$".listed-item":1',
                'expectedIdentifier' => new ElementIdentifier('.listed-item', 1),
            ],
            'css class selector; position: 3' => [
                'identifierString' => '$".listed-item":3',
                'expectedIdentifier' => new ElementIdentifier('.listed-item', 3),
            ],
            'css class selector; position: -1' => [
                'identifierString' => '$".listed-item":-1',
                'expectedIdentifier' => new ElementIdentifier('.listed-item', -1),
            ],
            'css class selector; position: -3' => [
                'identifierString' => '$".listed-item":-3',
                'expectedIdentifier' => new ElementIdentifier('.listed-item', -3),
            ],
            'css class selector; position: first' => [
                'identifierString' => '$".listed-item":first',
                'expectedIdentifier' => new ElementIdentifier('.listed-item', 1),
            ],
            'css class selector; position: last' => [
                'identifierString' => '$".listed-item":last',
                'expectedIdentifier' => new ElementIdentifier('.listed-item', -1),
            ],
        ];
    }

    public function descendantIdentifierDataProvider(): array
    {
        return [
            'css parent > css child' => [
                'identifierString' => '$"{{ $".parent" }} .child"',
                'expectedIdentifier' => (new ElementIdentifier('.child'))
                    ->withParentIdentifier(new ElementIdentifier('.parent')),
            ],
            'css parent > xpath child' => [
                'identifierString' => '$"{{ $".parent" }} /child"',
                'expectedIdentifier' => (new ElementIdentifier('/child'))
                    ->withParentIdentifier(new ElementIdentifier('.parent')),
            ],
            'xpath parent > css child' => [
                'identifierString' => '$"{{ $"/parent" }} .child"',
                'expectedIdentifier' => (new ElementIdentifier('.child'))
                    ->withParentIdentifier(new ElementIdentifier('/parent')),
            ],
            'xpath parent > xpath child' => [
                'identifierString' => '$"{{ $"/parent" }} /child"',
                'expectedIdentifier' => (new ElementIdentifier('/child'))
                    ->withParentIdentifier(new ElementIdentifier('/parent')),
            ],
            'grandparent > parent > child' => [
                'string' => '$"{{ $"{{ $".grandparent" }} .parent" }} .child"',
                'expectedIdentifier' => (new ElementIdentifier('.child'))
                    ->withParentIdentifier(
                        (new ElementIdentifier('.parent'))
                            ->withParentIdentifier(new ElementIdentifier('.grandparent'))
                    ),
            ],
            'great-grandparent > grandparent > parent > child' => [
                'string' => '$"{{ $"{{ $"{{ $".great-grandparent" }} .grandparent }} .parent" }} .child"',
                'expectedIdentifier' => (new ElementIdentifier('.child'))
                    ->withParentIdentifier(
                        (new ElementIdentifier('.parent'))
                            ->withParentIdentifier(
                                (new ElementIdentifier('.grandparent'))
                                    ->withParentIdentifier(new ElementIdentifier('.great-grandparent'))
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
                'expectedIdentifier' => new ElementIdentifier('//*[@id="element-id"]'),
            ],
            'xpath attribute selector, position: null' => [
                'identifierString' => '$"//input[@type=\"submit\"]"',
                'expectedIdentifier' => new ElementIdentifier('//input[@type="submit"]'),
            ],
            'xpath attribute selector; position: 1' => [
                'identifierString' => '$"//input[@type=\"submit\"]":1',
                'expectedIdentifier' => new ElementIdentifier('//input[@type="submit"]', 1),
            ],
            'xpath attribute selector; position: 3' => [
                'identifierString' => '$"//input[@type=\"submit\"]":3',
                'expectedIdentifier' => new ElementIdentifier('//input[@type="submit"]', 3),
            ],
            'xpath attribute selector; position: -1' => [
                'identifierString' => '$"//input[@type=\"submit\"]":-1',
                'expectedIdentifier' => new ElementIdentifier('//input[@type="submit"]', -1),
            ],
            'xpath attribute selector; position: -3' => [
                'identifierString' => '$"//input[@type=\"submit\"]":-3',
                'expectedIdentifier' => new ElementIdentifier('//input[@type="submit"]', -3),
            ],
            'xpath attribute selector; position: first' => [
                'identifierString' => '$"//input[@type=\"submit\"]":first',
                'expectedIdentifier' => new ElementIdentifier('//input[@type="submit"]', 1),
            ],
            'xpath attribute selector; position: last' => [
                'identifierString' => '$"//input[@type=\"submit\"]":last',
                'expectedIdentifier' => new ElementIdentifier('//input[@type="submit"]', -1),
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
