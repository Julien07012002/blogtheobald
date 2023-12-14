<?php

namespace App\Tests\Form\DataTransformer;

use App\Entity\Tag;
use App\Form\DataTransformer\TagArrayToStringTransformer;
use App\Repository\TagRepository;
use PHPUnit\Framework\TestCase;


class TagArrayToStringTransformerTest extends TestCase
{
 
    public function testCreateTheRightAmountOfTags(): void
    {
        $tags = $this->getMockedTransformer()->reverseTransform('Hello, Demo, How');

        $this->assertCount(3, $tags);
        $this->assertSame('Hello', $tags[0]->getName());
    }

 
    public function testCreateTheRightAmountOfTagsWithTooManyCommas(): void
    {
        $transformer = $this->getMockedTransformer();

        $this->assertCount(3, $transformer->reverseTransform('Hello, Demo,, How'));
        $this->assertCount(3, $transformer->reverseTransform('Hello, Demo, How,'));
    }

    public function testTrimNames(): void
    {
        $tags = $this->getMockedTransformer()->reverseTransform('   Hello   ');

        $this->assertSame('Hello', $tags[0]->getName());
    }

 
    public function testDuplicateNames(): void
    {
        $tags = $this->getMockedTransformer()->reverseTransform('Hello, Hello, Hello');

        $this->assertCount(1, $tags);
    }


    public function testUsesAlreadyDefinedTags(): void
    {
        $persistedTags = [
            $this->createTag('Hello'),
            $this->createTag('World'),
        ];
        $tags = $this->getMockedTransformer($persistedTags)->reverseTransform('Hello, World, How, Are, You');

        $this->assertCount(5, $tags);
        $this->assertSame($persistedTags[0], $tags[0]);
        $this->assertSame($persistedTags[1], $tags[1]);
    }


    public function testTransform(): void
    {
        $persistedTags = [
            $this->createTag('Hello'),
            $this->createTag('World'),
        ];
        $transformed = $this->getMockedTransformer()->transform($persistedTags);

        $this->assertSame('Hello,World', $transformed);
    }


    private function getMockedTransformer(array $findByReturnValues = []): TagArrayToStringTransformer
    {
        $tagRepository = $this->getMockBuilder(TagRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tagRepository->expects($this->any())
            ->method('findBy')
            ->willReturn($findByReturnValues);

        return new TagArrayToStringTransformer($tagRepository);
    }

    private function createTag(string $name): Tag
    {
        $tag = new Tag();
        $tag->setName($name);

        return $tag;
    }
}
