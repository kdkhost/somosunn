<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Event;
use App\Models\JobVacancy;
use App\Models\Mentorship;
use App\Models\Order;
use App\Models\Partner;
use Illuminate\Support\Facades\Auth;

class InstructorController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $coursesCount = Course::query()->where('user_id', $user->id)->count();
        $mentorshipsCount = Mentorship::query()->where('mentor_id', $user->id)->count();
        $eventsCount = Event::query()->where('user_id', $user->id)->count();
        $jobsCount = JobVacancy::query()->where('user_id', $user->id)->count();

        $myCourseIds = Course::query()->where('user_id', $user->id)->pluck('id');
        $myMentorshipIds = Mentorship::query()->where('mentor_id', $user->id)->pluck('id');
        $myEventIds = Event::query()->where('user_id', $user->id)->pluck('id');

        $certificatesCount = Certificate::query()
            ->where(function ($query) use ($myCourseIds, $myMentorshipIds, $myEventIds) {
                $query->whereIn('course_id', $myCourseIds)
                    ->orWhereIn('mentorship_id', $myMentorshipIds)
                    ->orWhereIn('event_id', $myEventIds);
            })
            ->count();

        $paidSalesQuery = Order::query()
            ->where('seller_id', $user->id)
            ->where('status', 'paid');

        $salesCount = (int) (clone $paidSalesQuery)->count();
        $grossSalesTotal = (float) (clone $paidSalesQuery)->sum('total_amount');
        $platformFeeTotal = (float) (clone $paidSalesQuery)->sum('platform_fee_amount');
        $netSalesTotal = max(0, $grossSalesTotal - $platformFeeTotal);

        $partner = Partner::query()->where('user_id', $user->id)->first();
        $partnerCouponsCount = $partner ? $partner->coupons()->count() : 0;

        $access = [
            'courses' => $user->hasPermission('courses.view') || $user->canAccessFeature('courses_access'),
            'mentorships' => $user->hasPermission('mentorships.view') || $user->canAccessFeature('mentorships_access'),
            'events' => $user->hasPermission('events.view') || $user->canAccessFeature('events_access'),
            'exhibitors' => method_exists($user, 'canManageEventExhibitors') && $user->canManageEventExhibitors(),
            'certificates' => $user->hasPermission('certificates.view') || $user->canAccessFeature('certificates_access'),
            'jobs' => $user->canAccessFeature('vagas_create'),
            'marketplace' => $user->canSellOnMarketplace(),
            'partnerCoupons' => (bool) $partner,
        ];

        return view('panel.instructor.index', compact(
            'coursesCount',
            'mentorshipsCount',
            'eventsCount',
            'jobsCount',
            'certificatesCount',
            'salesCount',
            'grossSalesTotal',
            'netSalesTotal',
            'partnerCouponsCount',
            'access'
        ));
    }
}
