<?php

declare(strict_types=1);

namespace src\services;

use src\forms\SearchForm;

class NeighboringService
{
    public function handle($paragraphID, $num): SearchForm
    {

        $form = new SearchForm();
        $form->matching = 'in';
        $form->dictionary = false;
        $form->query = implode(',', $this->getList((int)$paragraphID, (int)$num));
        return $form;
    }

    public function getList(int $paragraphID, int $num): array
    {
        $forward = $paragraphID;
        $backward = $paragraphID;
        $forwardList = [];
        $backwardList = [];
        for ($n = 1; $n <= $num; $n++) {
            if ($backward > 1) {
                $backwardList[] = --$backward;
            }
            $forwardList[] = ++$forward;
        }

        $result = array_merge($backwardList, [$paragraphID], $forwardList);
        asort($result);
        return $result;
    }
}
