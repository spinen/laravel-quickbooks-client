<?php

namespace Spinen\QuickBooks;

use Spinen\QuickBooks\Stubs\User;

class HasQuickBooksTokenTest extends TestCase
{
    /**
     * @var User
     */
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = new User();
    }

    /**
     * @test
     */
    public function it_can_be_constructed()
    {
        $this->assertInstanceOf(User::class, $this->user);
    }

    /**
     * @test
     */
    public function it_has_a_hasOne_relationship_to_token()
    {
        // The stub is just returing the relationship name, so making sure that it is the Token class
        $this->assertEquals(Token::class, $this->user->quickBooksToken());
    }
}
