<?php

namespace App\Http\Controllers\Panel\Admin;

class EventCouponController extends \App\Http\Controllers\Admin\EventCouponController
{
    protected string $viewPrefix = 'panel.admin.events.coupons';
    protected string $routePrefix = 'panel.admin.events.coupons';
    protected string $eventsRoutePrefix = 'panel.admin.events';
}
