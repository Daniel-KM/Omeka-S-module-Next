<?php declare(strict_types=1);

namespace Next\View\Helper;

use Laminas\View\Helper\AbstractHelper;

/**
 * View helper to get the user site slugs.
 */
class UserSiteSlugs extends AbstractHelper
{
    /**
     * @var array
     */
    protected $userSiteSlugs;

    /**
     * Construct the helper.
     *
     * @param string[] $userSiteSlugs
     */
    public function __construct($userSiteSlugs)
    {
        $this->userSiteSlugs = $userSiteSlugs;
    }

    /**
     * Return the user site slugs by site id.
     *
     * @return string[] User site slugs by site id.
     */
    public function __invoke(): array
    {
        return $this->userSiteSlugs;
    }
}
