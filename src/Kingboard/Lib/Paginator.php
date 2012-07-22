<?php
namespace Kingboard\Lib;
class Paginator
{
    private $currentPage = 1;
    private $count = 0;

    public function __construct($currentPage, $count)
    {
        $this->count = $count;
        if ($currentPage > $this->getLastPage())
        {
            $currentPage = $this->getLastPage();
        }
        $this->currentPage = $currentPage;
    }

    public function getKillsPerPage()
    {
        return $killsPerPage = \King23\Core\Registry::getInstance()->killListConfig["perPage"];
    }

    public function getSkip()
    {
        return ($this->currentPage - 1) * $this->getKillsPerPage();
    }

    public function getLastPage()
    {
        $lastPage = ceil($this->count / $this->getKillsPerPage());
        return ($lastPage < 1) ? 1: $lastPage;
    }

    public function getPreviousPage()
    {
        return $this->currentPage > 1 ? $this->currentPage - 1 : false;
    }

    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    public function getNextPage()
    {
        return ($this->getSkip() + $this->getKillsPerPage() < $this->count) ? $this->currentPage + 1 : false;
    }

    public function getNavArray() {
        return array(
            'next' => $this->getNextPage(),
            'prev' => $this->getPreviousPage(),
            'currentPage' => $this->getCurrentPage(),
            'lastPage' => $this->getLastPage()
        );
    }
}