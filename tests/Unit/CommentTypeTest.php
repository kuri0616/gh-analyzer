<?php

namespace Tests\Unit;

use App\Domain\Model\CommentType;
use PHPUnit\Framework\TestCase;
use ValueError;

class CommentTypeTest extends TestCase
{
    public function testValidCommentTypes(): void
    {
        $reviewInline = CommentType::REVIEW_INLINE;
        $issue = CommentType::ISSUE;
        
        $this->assertEquals('review_inline', $reviewInline->value);
        $this->assertEquals('issue', $issue->value);
    }

    public function testFromStringValid(): void
    {
        $reviewInline = CommentType::from('review_inline');
        $issue = CommentType::from('issue');
        
        $this->assertEquals(CommentType::REVIEW_INLINE, $reviewInline);
        $this->assertEquals(CommentType::ISSUE, $issue);
    }

    public function testFromStringInvalid(): void
    {
        $this->expectException(ValueError::class);
        
        CommentType::from('invalid_type');
    }

    public function testGetDisplayName(): void
    {
        $this->assertEquals('Review Comment', CommentType::REVIEW_INLINE->getDisplayName());
        $this->assertEquals('Issue Comment', CommentType::ISSUE->getDisplayName());
    }

    public function testTryFromValid(): void
    {
        $reviewInline = CommentType::tryFrom('review_inline');
        $issue = CommentType::tryFrom('issue');
        
        $this->assertEquals(CommentType::REVIEW_INLINE, $reviewInline);
        $this->assertEquals(CommentType::ISSUE, $issue);
    }

    public function testTryFromInvalid(): void
    {
        $result = CommentType::tryFrom('invalid_type');
        
        $this->assertNull($result);
    }

    public function testAllCases(): void
    {
        $cases = CommentType::cases();
        
        $this->assertCount(2, $cases);
        $this->assertContains(CommentType::REVIEW_INLINE, $cases);
        $this->assertContains(CommentType::ISSUE, $cases);
    }
}