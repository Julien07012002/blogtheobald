<?php



namespace App\Tests\Command;

use App\Command\AddUserCommand;
use App\Repository\UserRepository;

class AddUserCommandTest extends AbstractCommandTest
{
    private $userData = [
        'username' => 'chuck_norris',
        'password' => 'foobar',
        'email' => 'chuck@norris.com',
        'full-name' => 'Chuck Norris',
    ];

    protected function setUp(): void
    {
        if ('Windows' === \PHP_OS_FAMILY) {
            $this->markTestSkipped('`stty` is required to test this command.');
        }
    }

   
    public function testCreateUserNonInteractive(bool $isAdmin): void
    {
        $input = $this->userData;
        if ($isAdmin) {
            $input['--admin'] = 1;
        }
        $this->executeCommand($input);

        $this->assertUserCreated($isAdmin);
    }

    
    public function testCreateUserInteractive(bool $isAdmin): void
    {
        $this->executeCommand(
        
            $isAdmin ? ['--admin' => 1] : [],
           
            array_values($this->userData)
        );

        $this->assertUserCreated($isAdmin);
    }

    
    public function isAdminDataProvider(): ?\Generator
    {
        yield [false];
        yield [true];
    }

    
    private function assertUserCreated(bool $isAdmin): void
    {
        /** @var \App\Entity\User $user */
        $user = $this->getContainer()->get(UserRepository::class)->findOneByEmail($this->userData['email']);
        $this->assertNotNull($user);

        $this->assertSame($this->userData['full-name'], $user->getFullName());
        $this->assertSame($this->userData['username'], $user->getUsername());
        $this->assertTrue($this->getContainer()->get('test.user_password_hasher')->isPasswordValid($user, $this->userData['password']));
        $this->assertSame($isAdmin ? ['ROLE_ADMIN'] : ['ROLE_USER'], $user->getRoles());
    }

    protected function getCommandFqcn(): string
    {
        return AddUserCommand::class;
    }
}
