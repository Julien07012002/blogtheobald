<?php



namespace App\Tests\Controller\Admin;

use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;


class BlogControllerTest extends WebTestCase
{
    
    public function testAccessDeniedForRegularUsers(string $httpMethod, string $url): void
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'john_user',
            'PHP_AUTH_PW' => 'kitten',
        ]);

        $client->request($httpMethod, $url);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function getUrlsForRegularUsers(): ?\Generator
    {
        yield ['GET', '/en/admin/post/'];
        yield ['GET', '/en/admin/post/1'];
        yield ['GET', '/en/admin/post/1/edit'];
        yield ['POST', '/en/admin/post/1/delete'];
    }

    public function testAdminBackendHomePage(): void
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'jane_admin',
            'PHP_AUTH_PW' => 'kitten',
        ]);
        $client->request('GET', '/en/admin/post/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists(
            'body#admin_post_index #main tbody tr',
            'The backend homepage displays all the available posts.'
        );
    }

    
    public function testAdminNewPost(): void
    {
        $postTitle = 'Blog Post Title '.mt_rand();
        $postSummary = $this->generateRandomString(255);
        $postContent = $this->generateRandomString(1024);

        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'jane_admin',
            'PHP_AUTH_PW' => 'kitten',
        ]);
        $client->request('GET', '/en/admin/post/new');
        $client->submitForm('Create post', [
            'post[title]' => $postTitle,
            'post[summary]' => $postSummary,
            'post[content]' => $postContent,
        ]);

        $this->assertResponseRedirects('/en/admin/post/', Response::HTTP_FOUND);

        /** @var \App\Entity\Post $post */
        $post = static::getContainer()->get(PostRepository::class)->findOneByTitle($postTitle);
        $this->assertNotNull($post);
        $this->assertSame($postSummary, $post->getSummary());
        $this->assertSame($postContent, $post->getContent());
    }

    public function testAdminNewDuplicatedPost(): void
    {
        $postTitle = 'Blog Post Title '.mt_rand();
        $postSummary = $this->generateRandomString(255);
        $postContent = $this->generateRandomString(1024);

        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'jane_admin',
            'PHP_AUTH_PW' => 'kitten',
        ]);
        $crawler = $client->request('GET', '/en/admin/post/new');
        $form = $crawler->selectButton('Create post')->form([
            'post[title]' => $postTitle,
            'post[summary]' => $postSummary,
            'post[content]' => $postContent,
        ]);
        $client->submit($form);

        
        $client->submit($form);

        $this->assertSelectorTextSame('form .form-group.has-error label', 'Title');
        $this->assertSelectorTextContains('form .form-group.has-error .help-block', 'This title was already used in another blog post, but they must be unique.');
    }

    public function testAdminShowPost(): void
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'jane_admin',
            'PHP_AUTH_PW' => 'kitten',
        ]);
        $client->request('GET', '/en/admin/post/1');

        $this->assertResponseIsSuccessful();
    }

    
    public function testAdminEditPost(): void
    {
        $newBlogPostTitle = 'Blog Post Title '.mt_rand();

        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'jane_admin',
            'PHP_AUTH_PW' => 'kitten',
        ]);
        $client->request('GET', '/en/admin/post/1/edit');
        $client->submitForm('Save changes', [
            'post[title]' => $newBlogPostTitle,
        ]);

        $this->assertResponseRedirects('/en/admin/post/1/edit', Response::HTTP_FOUND);

        /** @var \App\Entity\Post $post */
        $post = static::getContainer()->get(PostRepository::class)->find(1);
        $this->assertSame($newBlogPostTitle, $post->getTitle());
    }

   
    public function testAdminDeletePost(): void
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'jane_admin',
            'PHP_AUTH_PW' => 'kitten',
        ]);
        $crawler = $client->request('GET', '/en/admin/post/1');
        $client->submit($crawler->filter('#delete-form')->form());

        $this->assertResponseRedirects('/en/admin/post/', Response::HTTP_FOUND);

        $post = static::getContainer()->get(PostRepository::class)->find(1);
        $this->assertNull($post);
    }

    private function generateRandomString(int $length): string
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return mb_substr(str_shuffle(str_repeat($chars, ceil($length / mb_strlen($chars)))), 1, $length);
    }
}
