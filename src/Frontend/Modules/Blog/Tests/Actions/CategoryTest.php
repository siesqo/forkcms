<?php

namespace Frontend\Modules\Blog\Actions;

use Backend\Modules\Blog\DataFixtures\LoadBlogCategories;
use Backend\Modules\Blog\DataFixtures\LoadBlogPosts;
use Frontend\Core\Tests\FrontendWebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

class CategoryTest extends FrontendWebTestCase
{
    public function testCategoryHasPage(Client $client): void
    {
        $this->loadFixtures(
            $client,
            [
                LoadBlogCategories::class,
                LoadBlogPosts::class,
            ]
        );

        $this->assertPageLoadedCorrectly($client, '/en/blog/category/' . LoadBlogCategories::BLOG_CATEGORY_SLUG, [LoadBlogCategories::BLOG_CATEGORY_TITLE]);
    }

    public function testNonExistingCategoryPostGives404(Client $client): void
    {
        $this->assertHttpStatusCode404($client, '/en/blog/category/non-existing');
    }

    public function testCategoryPageContainsBlogPost(Client $client): void
    {
        $this->loadFixtures(
            $client,
            [
                LoadBlogCategories::class,
                LoadBlogPosts::class,
            ]
        );

        $this->assertPageLoadedCorrectly($client, '/en/blog/category/' . LoadBlogCategories::BLOG_CATEGORY_SLUG, [LoadBlogCategories::BLOG_CATEGORY_TITLE]);
        $link = $client->getCrawler()->selectLink(LoadBlogPosts::BLOG_POST_TITLE)->link();
        $this->assertPageLoadedCorrectly($client, $link->getUri(), [LoadBlogPosts::BLOG_POST_TITLE]);
        $this->assertCurrentUrlEndsWith($client, '/en/blog/detail/' . LoadBlogPosts::BLOG_POST_SLUG);
    }

    public function testNonExistingPageGives404(Client $client): void
    {
        $this->loadFixtures(
            $client,
            [
                LoadBlogCategories::class,
                LoadBlogPosts::class,
            ]
        );

        $this->assertPageLoadedCorrectly($client, '/en/blog/category/' . LoadBlogCategories::BLOG_CATEGORY_SLUG, [LoadBlogCategories::BLOG_CATEGORY_TITLE]);
        $this->assertHttpStatusCode404($client, '/en/blog/category/' . LoadBlogCategories::BLOG_CATEGORY_SLUG, 'GET', ['page' => 34]);
    }
}
