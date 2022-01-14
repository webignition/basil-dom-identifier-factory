<?php

declare(strict_types=1);

namespace webignition\BasilDomIdentifierFactory\Tests\Unit;

use webignition\BasilDomIdentifierFactory\Factory;
use webignition\DomElementIdentifier\AttributeIdentifier;
use webignition\DomElementIdentifier\ElementIdentifier;
use webignition\DomElementIdentifier\ElementIdentifierInterface;

class FactoryTest extends \PHPUnit\Framework\TestCase
{
    private Factory $factory;

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
    ): void {
        $identifier = $this->factory->createFromIdentifierString($identifierString);

        $this->assertEquals($expectedIdentifier, $identifier);
    }

    /**
     * @return array<mixed>
     */
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

    /**
     * @return array<mixed>
     */
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
            'css attribute-based selector: has attribute' => [
                'identifierString' => '$".selector[attribute]"',
                'expectedIdentifier' => new ElementIdentifier('.selector[attribute]'),
            ],
            'css attribute-based selector: attribute equals, unquoted' => [
                'identifierString' => '$".selector[attribute=value]"',
                'expectedIdentifier' => new ElementIdentifier('.selector[attribute=value]'),
            ],
            'css attribute-based selector: attribute equals, quoted' => [
                'identifierString' => '$".selector[attribute=\"value\"]"',
                'expectedIdentifier' => new ElementIdentifier('.selector[attribute="value"]'),
            ],
            'css attribute-based selector: attribute equals, url value' => [
                'identifierString' => '$".selector[attribute=http://example.com]"',
                'expectedIdentifier' => new ElementIdentifier('.selector[attribute=http://example.com]'),
            ],
            'css attribute-based selector: attribute list contains' => [
                'identifierString' => '$".selector[attribute~=value]"',
                'expectedIdentifier' => new ElementIdentifier('.selector[attribute~=value]'),
            ],
            'css attribute-based selector: attribute list contains, url value' => [
                'identifierString' => '$".selector[attribute~=http://example.com]"',
                'expectedIdentifier' => new ElementIdentifier('.selector[attribute~=http://example.com]'),
            ],
            'css attribute-based selector: attribute equals or hyphen equals' => [
                'identifierString' => '$".selector[attribute|=value]"',
                'expectedIdentifier' => new ElementIdentifier('.selector[attribute|=value]'),
            ],
            'css attribute-based selector: attribute equals or hyphen equals, url value' => [
                'identifierString' => '$".selector[attribute|=http://example.com]"',
                'expectedIdentifier' => new ElementIdentifier('.selector[attribute|=http://example.com]'),
            ],
            'css attribute-based selector: attribute prefixed by' => [
                'identifierString' => '$".selector[attribute^=value]"',
                'expectedIdentifier' => new ElementIdentifier('.selector[attribute^=value]'),
            ],
            'css attribute-based selector: attribute prefixed by, url value' => [
                'identifierString' => '$".selector[attribute^=http://example.com]"',
                'expectedIdentifier' => new ElementIdentifier('.selector[attribute^=http://example.com]'),
            ],
            'css attribute-based selector: attribute ends with' => [
                'identifierString' => '$".selector[attribute$=value]"',
                'expectedIdentifier' => new ElementIdentifier('.selector[attribute$=value]'),
            ],
            'css attribute-based selector: attribute ends with, url value' => [
                'identifierString' => '$".selector[attribute$=http://example.com]"',
                'expectedIdentifier' => new ElementIdentifier('.selector[attribute$=http://example.com]'),
            ],
            'css attribute-based selector: attribute contains' => [
                'identifierString' => '$".selector[attribute*=value]"',
                'expectedIdentifier' => new ElementIdentifier('.selector[attribute*=value]'),
            ],
            'css attribute-based selector: attribute contains, url value' => [
                'identifierString' => '$".selector[attribute*=http://example.com]"',
                'expectedIdentifier' => new ElementIdentifier('.selector[attribute*=http://example.com]'),
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    public function descendantIdentifierDataProvider(): array
    {
        return [
            'css parent >> css child' => [
                'identifierString' => '$".parent" >> $".child"',
                'expectedIdentifier' => (new ElementIdentifier('.child'))
                    ->withParentIdentifier(new ElementIdentifier('.parent')),
            ],
            'css parent >> xpath child' => [
                'identifierString' => '$".parent" >> $"/child"',
                'expectedIdentifier' => (new ElementIdentifier('/child'))
                    ->withParentIdentifier(new ElementIdentifier('.parent')),
            ],
            'xpath parent >> css child' => [
                'identifierString' => '$"/parent" >> $".child"',
                'expectedIdentifier' => (new ElementIdentifier('.child'))
                    ->withParentIdentifier(new ElementIdentifier('/parent')),
            ],
            'xpath parent >> xpath child' => [
                'identifierString' => '$"/parent" >> $"/child"',
                'expectedIdentifier' => (new ElementIdentifier('/child'))
                    ->withParentIdentifier(new ElementIdentifier('/parent')),
            ],
            'grandparent >> parent >> child' => [
                'string' => '$".grandparent" >> $".parent" >> $".child"',
                'expectedIdentifier' => (new ElementIdentifier('.child'))
                    ->withParentIdentifier(
                        (new ElementIdentifier('.parent'))
                            ->withParentIdentifier(new ElementIdentifier('.grandparent'))
                    ),
            ],
            'great-grandparent >> grandparent >> parent >> child' => [
                'string' => '$".great-grandparent" >> $".grandparent" >> $".parent" >> $".child"',
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

    /**
     * @return array<mixed>
     */
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
    public function testCreateFromIdentifierStringReturnsNull(string $identifierString): void
    {
        $this->assertNull($this->factory->createFromIdentifierString($identifierString));
    }

    /**
     * @return array<mixed>
     */
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
