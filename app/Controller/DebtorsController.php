<?php

namespace Controller;

use Model\Resident;
use Src\Request;
use Src\View;

class DebtorsController {
    public function debtors(Request $request): string
    {
        return (new View())->render('site.debtors', [
            'debtors' => Resident::get_debtors(),
            'request' => $request
        ]);
    }
}