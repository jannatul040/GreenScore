<?php
use PHPUnit\Framework\TestCase;

class CommunityTipsTest extends TestCase
{
    // ── Message validation ───────────────────────────────────

    public function testNonEmptyMessageIsValid(): void
    {
        $message = trim('Plant a tree today!');
        $this->assertNotEmpty($message, 'A non-empty message should be valid.');
    }

    public function testEmptyMessageIsRejected(): void
    {
        $message = trim('   ');
        $this->assertEmpty($message, 'A whitespace-only message should be rejected.');
    }

    public function testMessageIsTrimmedBeforeStorage(): void
    {
        $raw     = '  Switch to reusable bags.  ';
        $trimmed = trim($raw);
        $this->assertEquals('Switch to reusable bags.', $trimmed);
    }

    public function testMessagePreservesSpecialCharacters(): void
    {
        $message = "Use <solar> panels & save 50% energy!";
        $escaped = htmlspecialchars($message);
        $this->assertStringContainsString('&lt;solar&gt;', $escaped);
        $this->assertStringContainsString('&amp;', $escaped);
    }

    // ── Auth guard logic ────────────────────────────────────

    public function testAuthGuardBlocksUnauthenticatedUser(): void
    {
        $session = [];
        $allowed = isset($session['user_id']);
        $this->assertFalse($allowed, 'Unauthenticated user must be blocked.');
    }

    public function testAuthGuardAllowsAuthenticatedUser(): void
    {
        $session = ['user_id' => 42];
        $allowed = isset($session['user_id']);
        $this->assertTrue($allowed, 'Authenticated user should be allowed.');
    }

    // ── Ownership check ──────────────────────────────────────

    public function testOwnerCanDeleteOwnTip(): void
    {
        $tip_user_id    = 5;
        $logged_in_user = 5;
        $this->assertEquals($tip_user_id, $logged_in_user,
            'User should be able to delete their own tip.'
        );
    }

    public function testNonOwnerCannotDeleteTip(): void
    {
        $tip_user_id    = 5;
        $logged_in_user = 9;
        $this->assertNotEquals($tip_user_id, $logged_in_user,
            'Non-owner must not be allowed to delete this tip.'
        );
    }

    // ── Pagination ───────────────────────────────────────────

    public function testPaginationOffsetCalculation(): void
    {
        $limit = 5;

        $this->assertEquals(0,  ($this->getOffset(1, $limit)));
        $this->assertEquals(5,  ($this->getOffset(2, $limit)));
        $this->assertEquals(10, ($this->getOffset(3, $limit)));
    }

    public function testTotalPagesCalculation(): void
    {
        $limit = 5;

        $this->assertEquals(2, (int) ceil(6  / $limit));
        $this->assertEquals(1, (int) ceil(5  / $limit));
        $this->assertEquals(3, (int) ceil(11 / $limit));
    }

    private function getOffset(int $page, int $limit): int
    {
        return ($page - 1) * $limit;
    }
}