<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DesignSystemController extends Controller
{
    public function index(): View
    {
        return view('admin.pages.design-system');
    }

    public function shop(): View
    {
        return view('design.v6.shop-preview', ['products' => $this->previewProducts()]);
    }

    public function account(): View
    {
        return view('design.v6.account-preview');
    }

    public function admin(): View
    {
        return view('design.v6.admin-preview');
    }

    private function previewProducts(): array
    {
        return [
            ['serial_number' => 'EB-SCH-001', 'title' => 'Ipari kismegszakító 3P', 'price' => 'Partnerár után'],
            ['serial_number' => 'EB-CON-024', 'title' => 'CEE csatlakozó 32 A', 'price' => 'Partnerár után'],
            ['serial_number' => 'EB-CBL-116', 'title' => 'Hajlékony ipari kábel', 'price' => 'Partnerár után'],
        ];
    }
}
