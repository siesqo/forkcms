<?php

namespace Frontend\Core\Engine;

use Common\Uri as CommonUri;

/**
 * Frontend RSS item.
 */
final class RssItem
{
    private string $title;
    private string $link;
    private string $description;
    private string $author = '';
    private string $guid = '';
    private bool $guidIsPermaLink = true;
    private ?int $pubDate = null;
    private array $categories = [];

    private array $utm = ['utm_source' => 'feed', 'utm_medium' => 'rss'];

    public function __construct(string $title, string $link, string $content)
    {
        $this->title = htmlspecialchars_decode($title);
        $this->description = htmlspecialchars_decode($content);
        $this->utm['utm_campaign'] = CommonUri::getUrl($title);

        $this->link = Model::addUrlParameters($link, $this->utm, '&amp;');
        $this->setGuid($link, true);
    }

    public function addCategory(string $name): void
    {
        $this->categories[] = $name;
    }

    public function setAuthor(string $author): void
    {
        $author = htmlspecialchars_decode($author);

        if (!filter_var($author, FILTER_VALIDATE_EMAIL)) {
            $author = CommonUri::getUrl($author) . '@example.com (' . $author . ')';
        }

        $this->author = $author;
    }

    public function setDescription(string $description): void
    {
        $this->description = $this->processLinks(htmlspecialchars_decode($description));
    }

    public function setGuid(string $link, bool $isPermaLink = true): void
    {
        $this->guid = $this->prependWithSiteUrlIfHttpIsMissing($link);
        $this->guidIsPermaLink = $isPermaLink;
    }

    public function setLink(string $link): void
    {
        $this->link = $this->prependWithSiteUrlIfHttpIsMissing($link);
    }

    public function setPublicationDate(int|string $publicationDate): void
    {
        $this->pubDate = is_int($publicationDate) ? $publicationDate : (int) strtotime($publicationDate);
    }

    public function processLinks(string $content): string
    {
        $content = str_replace(['href="/', 'src="/'], ['href="' . SITE_URL . '/', 'src="' . SITE_URL . '/'], $content);

        if (!preg_match_all('/href="(http:\/\/(.*))"/iU', $content, $matches)) {
            return $content;
        }

        $searchLinks = [];
        $replaceLinks = [];
        foreach ((array) $matches[1] as $i => $link) {
            $searchLinks[] = $matches[0][$i];
            $replaceLinks[] = 'href="' . Model::addUrlParameters($link, $this->utm, '&amp;') . '"';
        }

        return str_replace($searchLinks, $replaceLinks, $content);
    }

    public function toDomElement(\DOMDocument $dom): \DOMElement
    {
        $item = $dom->createElement('item');

        $item->appendChild($dom->createElement('title', $this->title));
        $item->appendChild($dom->createElement('link', $this->link));

        $desc = $dom->createElement('description');
        $desc->appendChild($dom->createCDATASection($this->description));
        $item->appendChild($desc);

        if ($this->pubDate !== null) {
            $item->appendChild($dom->createElement('pubDate', date('r', $this->pubDate)));
        }
        if ($this->author !== '') {
            $item->appendChild($dom->createElement('author', $this->author));
        }
        foreach ($this->categories as $category) {
            $item->appendChild($dom->createElement('category', $category));
        }
        if ($this->guid !== '') {
            $guidEl = $dom->createElement('guid', $this->guid);
            $guidEl->setAttribute('isPermaLink', $this->guidIsPermaLink ? 'true' : 'false');
            $item->appendChild($guidEl);
        }

        return $item;
    }

    private function prependWithSiteUrlIfHttpIsMissing(string $link): string
    {
        if (!Model::getContainer()->get('fork.validator.url')->isExternalUrl($link)) {
            return SITE_URL . $link;
        }

        return $link;
    }
}
