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

        $siteHTMLEndOfBody = (string) $this->get('fork.settings')->get('Core', 'site_html_end_of_body', $this->get('fork.settings')->get('Core', 'site_html_footer', null));

        // @deprecated remove this in Fork 6, Facebook should not be added automaticall
        $facebookAppId = $this->get('fork.settings')->get('Core', 'facebook_app_id', null);
        if ($facebookAppId !== null) {
            // add Facebook container
            $siteHTMLEndOfBody .= $this->getFacebookHtml($facebookAppId);
        }

        // assign site wide html
        $this->template->assignGlobal('siteHTMLEndOfBody', $siteHTMLEndOfBody);

        // @deprecated remove this in Fork 6, use siteHTMLEndOfBody
        $this->template->assignGlobal('siteHTMLFooter', $siteHTMLEndOfBody);
    }

    /**
     * Builds the HTML needed for Facebook to be initialized
     * @deprecated remove this in Fork 6, Facebook should be added with respect
     *             to the given consent. In essence: add Facebook yourself if
     *             needed.
     *
     * @param string $facebookAppId The application id used to interact with FB
     *
     * @return string  HTML and JS needed to initialize FB JavaScript
     */
    protected function getFacebookHtml(string $facebookAppId): string
    {
        // add the fb-root div
        $facebookHtml = "\n" . '<div id="fb-root"></div>' . "\n";

        // add facebook JavaScript
        $facebookHtml .= '<script>' . "\n";
        if (!empty($facebookAppId)) {
            $facebookHtml .= '    window.fbAsyncInit = function() {' . "\n";
            $facebookHtml .= '        FB.init({' . "\n";
            $facebookHtml .= '            appId: "' . $facebookAppId . '",' . "\n";
            $facebookHtml .= '            status: true,' . "\n";
            $facebookHtml .= '            cookie: true,' . "\n";
            $facebookHtml .= '            xfbml: true,' . "\n";
            $facebookHtml .= '            oauth: true' . "\n";
            $facebookHtml .= '        });' . "\n";
            $facebookHtml .= '        jsFrontend.facebook.afterInit();' . "\n";
            $facebookHtml .= '    };' . "\n";
        }

        $facebookHtml .= '    (function(d, s, id){' . "\n";
        $facebookHtml .= '        var js, fjs = d.getElementsByTagName(s)[0];' . "\n";
        $facebookHtml .= '        if (d.getElementById(id)) {return;}' . "\n";
        $facebookHtml .= '        js = d.createElement(s); js.id = id;' . "\n";
        $facebookHtml .= '        js.src = "//connect.facebook.net/' . $this->getFacebookLocale() . '/all.js";' . "\n";
        $facebookHtml .= '        fjs.parentNode.insertBefore(js, fjs);' . "\n";
        $facebookHtml .= '    }(document, \'script\', \'facebook-jssdk\'));' . "\n";
        $facebookHtml .= '</script>';

        return $facebookHtml;
    }

    /**
     * @deprecated remove this in Fork 6, Facebook should be added with respect
     *             to the given consent. In essence: add Facebook yourself if
     *             needed.
     */
    private function getFacebookLocale(): string
    {
        $specialCases = [
            'en' => 'en_US', // sorry uk :( I prefer you too
            'zh' => 'zh_CN',
            'cs' => 'cs_CZ',
            'el' => 'el_GR',
            'ja' => 'ja_JP',
            'sv' => 'sv_SE',
            'uk' => 'uk_UA',
        ];

        // check if it is a special case, otherwise return [language]_[language]
        return $specialCases[LANGUAGE] ?? mb_strtolower(LANGUAGE) . '_' . mb_strtoupper(LANGUAGE);
    }
}
