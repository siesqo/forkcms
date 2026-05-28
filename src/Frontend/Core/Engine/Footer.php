<?php

namespace Frontend\Core\Engine;

use ForkCMS\App\KernelLoader;
use Symfony\Component\HttpKernel\KernelInterface;
use Frontend\Core\Engine\Navigation as FrontendNavigation;

/**
 * This class will be used to alter the footer-part of the HTML-document that will be created by the frontend.
 */
class Footer extends KernelLoader
{
    /**
     * TwigTemplate instance
     *
     * @var TwigTemplate
     */
    protected $template;

    /**
     * URL instance
     *
     * @var Url
     */
    protected $url;

    public function __construct(KernelInterface $kernel)
    {
        parent::__construct($kernel);

        $this->template = $this->getContainer()->get('templating');
        $this->url = $this->getContainer()->get('url');

        $this->getContainer()->set('footer', $this);
    }

    /**
     * Parse the footer into the template
     */
    public function parse(): void
    {
        $footerLinks = (array) Navigation::getFooterLinks();
        $this->template->assignGlobal('footerLinks', $footerLinks);

        $siteHTMLEndOfBody = (string) $this->get('fork.settings')->get('Core', 'site_html_end_of_body');

        // assign site wide html
        $this->template->assignGlobal('siteHTMLEndOfBody', $siteHTMLEndOfBody);
    }
}
