<?php

namespace Frontend\Modules\Policies\Widgets;

use Frontend\Core\Engine\Base\Widget as FrontendBaseWidget;

/**
 * This widget loads the website's cookie policy with data provided in the settings
 *
 * @author Alessandro Craeye <alessandro@siesqo.be>
 */
class CookiePolicy extends FrontendBaseWidget
{
    public function execute(): void
    {
        parent::execute();
        $this->loadTemplate();
        $this->template->assign('company', $this->get('fork.settings')->getForModule($this->module));
    }
}
