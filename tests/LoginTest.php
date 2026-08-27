<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/fake_login_tools.php';

class LoginTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['csrf_token'] = 'test_csrf_token';
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    public function testValidateSucceedsWithCorrectCredentials(): void
    {
        [$ok, $data] = validate(true, 'user@example.com', 'userpass123');

        $this->assertTrue($ok, 'Valid credentials should return true.');
        $this->assertEquals('user@example.com', $data['email']);
        $this->assertEquals('user', $data['role']);
        $this->assertEquals(2, $data['id']);
    }

    public function testValidateFailsWithWrongPassword(): void
    {
        [$ok, $errors] = validate(true, 'user@example.com', 'wrongpassword');

        $this->assertFalse($ok, 'Wrong password should return false.');
        $this->assertContains('Incorrect password.', $errors);
    }

    public function testValidateFailsWithUnknownEmail(): void
    {
        [$ok, $errors] = validate(true, 'nobody@example.com', 'password123');

        $this->assertFalse($ok);
        $this->assertContains('Email address and password not found.', $errors);
    }

    public function testValidateFailsWithEmptyEmail(): void
    {
        [$ok, $errors] = validate(true, '', 'password123');

        $this->assertFalse($ok);
        $this->assertContains('Enter your email address.', $errors);
    }

    public function testValidateFailsWithEmptyPassword(): void
    {
        [$ok, $errors] = validate(true, 'user@example.com', '');

        $this->assertFalse($ok);
        $this->assertContains('Enter your password.', $errors);
    }

    public function testAdminRoleReturnedCorrectly(): void
    {
        [$ok, $data] = validate(true, 'admin@example.com', 'adminpass123');

        $this->assertTrue($ok);
        $this->assertEquals('admin', $data['role']);
    }

    public function testPasswordVerifyUsedNotPlainComparison(): void
    {
        // Proves validate() uses password_verify() — plain text comparison would fail
        // because fake_login_tools stores bcrypt hashes, not plain passwords
        [$ok] = validate(true, 'user@example.com', 'userpass123');
        $this->assertTrue($ok, 'password_verify() should match the hashed password.');

        [$fail] = validate(true, 'user@example.com', 'USERPASS123');
        $this->assertFalse($fail, 'Password check must be case-sensitive.');
    }
}