<?php

namespace App\Http\Controllers\Panel\Admin;

use App\Http\Controllers\Admin\SplitController as BaseAdminSplitController;

class SplitController extends BaseAdminSplitController
{
    protected string $indexView = 'panel.admin.splits.index';
}
