<?php

namespace Frontend\Core\Engine;

use Common\Uri as CommonUri;

/**
 * Frontend RSS feed generator.
 */
final class Rss
{
    private string $title;
    private string $description;
    private string $link;
    private string $language = 'en';
    private string $copyright = '';
    private string $generator = '';
    private array $image = [];
    /** @var RssItem[] */
    private array $items;

    public function __construct(string $title, string $link, string $description, array $items = [])
    {
        $this->title = htmlspecialchars_decode($title);
        $this->description = htmlspecialchars_decode($description);
        $this->link = str_replace(
            '&',
            '&amp;',
            Model::addUrlParameters(
                $link,
                ['utm_source' => 'feed', 'utm_medium' => 'rss', 'utm_campaign' => CommonUri::getUrl($title)],
                '&amp;'
            )
        );
        $this->items = $items;

        $siteTitle = htmlspecialchars_decode(
            (string) Model::get('fork.settings')->get('Core', 'site_title_' . LANGUAGE)
        );

        $this->setLanguage(LANGUAGE);
        $this->setCopyright(date('Y') . ' ' . $siteTitle);
        $this->setGenerator($siteTitle);
        $this->setImage(SITE_URL . FRONTEND_CORE_URL . '/Layout/images/rss_image.png', $title, $link);

        if (Model::get('fork.settings')->get('Core', 'theme', null) === null) {
            return;
        }

        $theme = Model::get('fork.settings')->get('Core', 'theme', 'Fork');
        if (is_file(PATH_WWW . '/src/Frontend/Themes/' . $theme . '/Core/images/rss_image.png')) {
            $this->setImage(
                SITE_URL . '/src/Frontend/Themes/' . $theme . '/Core/images/rss_image.png',
                $title,
                $link
            );
        }
    }

    public function addItem(RssItem $item): void
    {
        $this->items[] = $item;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setLanguage(string $language): void
    {
        $this->language = $language;
    }

    public function setCopyright(string $copyright): void
    {
        $this->copyright = $copyright;
    }

    public function setGenerator(string $generator): void
    {
        $this->generator = $generator;
    }

    public function setImage(
        string $url,
        string $title,
        string $link,
        ?int $width = null,
        ?int $height = null,
        ?string $description = null
    ): void {
        $link = Model::addUrlParameters(
            $link,
            ['utm_source' => 'feed', 'utm_medium' => 'rss', 'utm_campaign' => CommonUri::getUrl($this->title)],
            '&amp;'
        );
        $this->image = compact('url', 'title', 'link');
    }

    public function parse(bool $headers = true): void
    {
        if ($headers) {
            header('Content-Type: application/xml; charset=utf-8');
        }
        echo $this->buildXml();
        exit;
    }

    private function buildXml(): string
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $rss = $dom->createElement('rss');
        $rss->setAttribute('version', '2.0');
        $dom->appendChild($rss);

        $channel = $dom->createElement('channel');
        $rss->appendChild($channel);

        $channel->appendChild($dom->createElement('title', $this->title));
        $channel->appendChild($dom->createElement('link', $this->link));

        $desc = $dom->createElement('description');
        $desc->appendChild($dom->createCDATASection($this->description));
        $channel->appendChild($desc);

        if ($this->language !== '') {
            $channel->appendChild($dom->createElement('language', $this->language));
        }
        if ($this->copyright !== '') {
            $channel->appendChild($dom->createElement('copyright', $this->copyright));
        }
        if ($this->generator !== '') {
            $channel->appendChild($dom->createElement('generator', $this->generator));
        }

        if (!empty($this->image)) {
            $imageEl = $dom->createElement('image');
            $imageEl->appendChild($dom->createElement('url', $this->image['url']));
            $imageEl->appendChild($dom->createElement('title', $this->image['title']));
            $imageEl->appendChild($dom->createElement('link', $this->image['link']));
            $channel->appendChild($imageEl);
        }

        foreach ($this->items as $item) {
            $channel->appendChild($item->toDomElement($dom));
        }

        return (string) $dom->saveXML();
    }
}
