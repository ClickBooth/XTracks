<?php

class Navigation
{
    private $_currentURI;
    private $_sections;

    public function addSection(Section $section)
    {
        $this->_sections[$section->getName()] = $section;
    }

    public function setCurrentURI($uri)
    {
        $this->_currentURI = $uri;
    }

    public function render()
    {
        $items = array();

        $items[] = "<div class=\"nav_container\">";

        foreach($this->_sections as $name=>$section) {
            //$active = '';

            $section->setCurrentURI($this->_currentURI);
            /*if ($section->isActive()) {
                $active = ' nav_active';
            }*/
            $items[] = '<div class="navbox">';
            $items[] = '<div class="navtitle">';
            $items[] = '<span class="box_title">'.$name.'</span>';
            $items[] = '<div class="box_toggle open">&nbsp;</div>';
            $items[] = '</div>';
            $items[] = $section;

            $items[] = '<div class="navbottom">';
            $items[] = '<div class="bottom_arrow">&nbsp;</div>';
            $items[] = '</div>';
            $items[] = '</div>';
        }
        $items[] = "</div>";

        return join("\n", $items);
    }

    public function __toString()
    {
        return $this->render();
    }
}

class Section
{
    private $_name;
    private $_primaryLink;
    private $_active;
    private $_currentURI;

    private $_links;

    public function __construct($name, $link)
    {
        $this->_active = false;
        $this->_name = $name;
        $this->_primaryLink = $link;
    }

    public function setCurrentURI($uri)
    {
        $this->_currentURI = $uri;

        foreach($this->_links as $name=>$link) {
            // Normalize links and do the check.
            if ($link == $uri) {
                $this->_active = true;
            }
        }
    }

    public function isActive()
    {
        return $this->_active;
    }

    public function addLink($name, $url)
    {
        $this->_links[$name] = $url;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function render()
    {
        $menu = array();

        //$menu[] = "<ul class=\"second_level_tab\">";
        $menu[] = '<div class="navbody">';
        $menu[] = '<ul class="second_level_tab">';
        foreach($this->_links as $name=>$link) {
            $active = '';

            // Normalize links and do the check.
            if ($link == $this->_currentURI) {
                $active = ' class="nav_active"';
                $this->_active = true;
            }


            $menu[] = sprintf('<li%s><a href="%s">%s</a></li>', $active, $link, $name);

        }
        $menu[] = '</ul>';
        $menu[] = '</div>';
        //$menu[] = "</ul>";

        return join("\n", $menu);
    }

    public function __toString()
    {
        return $this->render();
    }
}

$nav = new Navigation;

// Overview section
$s = new Section('Overview', '/tracking202');
$s->addLink('Campaign Overview', '/overview/');
$s->addLink('Breakdown Analysis', '/overview/breakdown.php');
$s->addLink('Day Parting', '/overview/day-parting.php');
$s->addLink('Week Parting', '/overview/week-parting.php');

$nav->addSection($s);
unset($s);

// Setup section
$s = new Section('Setup', '/setup');
$s->addLink('#1 PPC Accounts', '/setup/ppc_accounts.php');
//$s->addLink('#2 Aff Networks', '/setup/aff_networks.php');
$s->addLink('#2 Aff Campaigns', '/setup/aff_campaigns.php');
$s->addLink('#3 Landing Pages', '/setup/landing_pages.php');
$s->addLink('#4 Text Ads', '/setup/text_ads.php');
$s->addLink('#5 Get LP Code', '/setup/get_landing_code.php');
$s->addLink('#6 Get Links', '/setup/get_trackers.php');
//$s->addLink('#8 Get Postback/Pixel', '/setup/get_postback.php');

$nav->addSection($s);
unset($s);

// Analyze section
$s = new Section('Analyze', '/analyze');
$s->addLink('Keywords', '/analyze/keywords.php');
$s->addLink('Text Ads', '/analyze/text_ads.php');
$s->addLink('Referers', '/analyze/referers.php');
$s->addLink('IPs', '/analyze/ips.php');
$s->addLink('Landing Pages', '/analyze/landing_pages.php');
$nav->addSection($s);
unset($s);

// Visitors section
$s = new Section('Visitors', '/visitors/');
$s->addLink('Visitor History', '/visitors/');
$nav->addSection($s);
unset($s);

// Spy section
$s = new Section('Spy', '/spy/');
$s->addLink('Spy View', '/spy/');
$nav->addSection($s);
unset($s);

$s = new Section('Update', '/update');
$s->addLink('Update Subids', '/update/subids.php');
$s->addLink('Update CPC', '/update/cpc.php');
$s->addLink('Reset Campaign Subids', '/update/clear-subids.php');
$s->addLink('Delete Subids', '/update/delete-subids.php');
$s->addLink('Upload Revenue Reports', '/update/upload.php');
$nav->addSection($s);
unset($s);


$nav->setCurrentURI($_SERVER['REQUEST_URI']);

echo $nav->render();
