<?php


namespace App\Tests\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;


class DefaultControllerTest extends WebTestCase
{
   
    public function testPublicUrls(string $url): void
    {
        $client = static::createClient();
        $client->request('GET', $url);

        $this->assertResponseIsSuccessful(sprintf('The %s public URL loads correctly.', $url));
    }

    
    public function testPublicBlogPost(): void
    {
        $client = static::createClient();
        $blogPost = $client->getContainer()->get('doctrine')->getRepository(Post::class)->find(1);
        $client->request('GET', sprintf('/en/blog/posts/%s', $blogPost->getSlug()));

        $this->assertResponseIsSuccessful();
    }

    
    public function testSecureUrls(string $url): void
    {
        $client = static::createClient();
        $client->request('GET', $url);

        $this->assertResponseRedirects(
            'http://localhost/en/login',
            Response::HTTP_FOUND,
            sprintf('The %s secure URL redirects to the login form.', $url)
        );
    }

    public function getPublicUrls(): ?\Generator
    {
        yield ['/'];
        yield ['/en/blog/'];
        yield ['/en/login'];
    }

    public function getSecureUrls(): ?\Generator
    {
        yield ['/en/admin/post/'];
        yield ['/en/admin/post/new'];
        yield ['/en/admin/post/1'];
        yield ['/en/admin/post/1/edit'];
    }
}
