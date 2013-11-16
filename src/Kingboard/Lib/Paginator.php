<?php
namespace Kingboard\Lib;

use King23\Core\Registry;

/**
 *  class to simplify pagination
 */
class Paginator
{
    /**
     * @var int
     */
    private $currentPage = 1;

    /**
     * @var int
     */
    private $count = 0;

    /**
     * @param int $currentPage
     * @param int $count
     */
    public function __construct($currentPage, $count)
    {
        $this->count = $count;
        if ($currentPage > $this->getLastPage()) {
            $currentPage = $this->getLastPage();
        }
        $this->currentPage = $currentPage;
    }

    /**
     * get the amount of kills per page to display from the configuration
     * @return mixed
     */
    public function getKillsPerPage()
    {
        return Registry::getInstance()->killListConfig["perPage"];
    }

    /**
     * calculate on how many kills to skip
     * @return int
     */
    public function getSkip()
    {
        return ($this->currentPage - 1) * $this->getKillsPerPage();
    }

    /**
     * calculate the last possible page
     * @return int
     */
    public function getLastPage()
    {
        $lastPage = ceil($this->count / $this->getKillsPerPage());
        return ($lastPage < 1) ? 1 : $lastPage;
    }

    /**
     * calculate the previous page
     * @return bool|int
     */
    public function getPreviousPage()
    {
        return $this->currentPage > 1 ? $this->currentPage - 1 : false;
    }

    /**
     * return current page
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * calculate number of next page
     * @return bool|int
     */
    public function getNextPage()
    {
        return ($this->getSkip() + $this->getKillsPerPage() < $this->count) ? $this->currentPage + 1 : false;
    }

    /**
     * array with all necessary calculations done for navigation
     * @return array
     */
    public function getNavArray()
    {
        return array(
            'next' => $this->getNextPage(),
            'prev' => $this->getPreviousPage(),
            'currentPage' => $this->getCurrentPage(),
            'lastPage' => $this->getLastPage()
        );
    }
}
