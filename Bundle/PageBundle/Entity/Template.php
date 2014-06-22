<?php

namespace Victoire\Bundle\PageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Victoire\Bundle\PageBundle\Entity\BasePage;

/**
 * Template
 *
 * @ORM\Entity
 */
class Template extends BasePage
{
    const TYPE = 'template';

    /**
     * @var string
     *
     * @ORM\OneToMany(targetEntity="\Victoire\Bundle\PageBundle\Entity\BasePage", mappedBy="template")
     */
    protected $pages;

    /**
     * contruct
     **/
    public function __contruct()
    {
        $this->widgets = new \ArrayCollection();
    }

    /**
     * Set page
     *
     * @param string $pages
     * @return Template
     */
    public function setPages($pages)
    {
        $this->pages = $pages;

        return $this;
    }

    /**
     * Get pages
     *
     * @return string
     */
    public function getPages()
    {
        return $this->pages;
    }
}