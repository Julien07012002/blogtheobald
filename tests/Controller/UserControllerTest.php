<?php



namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;


class UserControllerTest extends WebTestCase
{
    
    public function testAccessDeniedForAnonymousUsers(string $httpMethod, string $url): void
    {
        $client = static::createClient();
        $client->request($httpMethod, $url);

        $this->assertResponseRedirects(
            'http://localhost/en/login',
            Response::HTTP_FOUND,
            sprintf('The %s secure URL redirects to the login form.', $url)
        );
    }

    public function getUrlsForAnonymousUsers(): ?\Generator
    {
        yield ['GET', '/en/profile/edit'];
        yield ['GET', '/en/profile/change-password'];
    }

    public function testEditUser(): void
    {
        $newUserEmail = 'admin_jane@symfony.com';

        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'jane_admin',
            'PHP_AUTH_PW' => 'kitten',
        ]);
        $client->request('GET', '/en/profile/edit');
        $client->submitForm('Save changes', [
            'user[email]' => $newUserEmail,
        ]);

        $this->assertResponseRedirects('/en/profile/edit', Response::HTTP_FOUND);

        /** @var \App\Entity\User $user */
        $user = static::getContainer()->get(UserRepository::class)->findOneByEmail($newUserEmail);

        $this->assertNotNull($user);
        $this->assertSame($newUserEmail, $user->getEmail());
    }

    public function testChangePassword(): void
    {
        $newUserPassword = 'new-password';

        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'jane_admin',
            'PHP_AUTH_PW' => 'kitten',
        ]);
        $client->request('GET', '/en/profile/change-password');
        $client->submitForm('Save changes', [
            'change_password[currentPassword]' => 'kitten',
            'change_password[newPassword][first]' => $newUserPassword,
            'change_password[newPassword][second]' => $newUserPassword,
        ]);

        $this->assertResponseRedirects(
            '/en/logout',
            Response::HTTP_FOUND,
            'Changing password logout the user.'
        );
    }
}
