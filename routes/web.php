<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\BlockController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
    Route::get('/health', function () {
        return response()->json(['status' => 'ok', 'timestamp' => now()->toISOString()]);
    })->name('health');

    Route::get('/test-email', function () {
        try {
            \Log::info('Test email: attempting send', ['mailer' => config('mail.default')]);
            \Illuminate\Support\Facades\Mail::raw('Test email from Skill Matching System at ' . now(), function ($m) {
                $m->to('batm6800@gmail.com')
                  ->subject('Test Email - Skill Matching');
            });
            \Log::info('Test email: sent successfully');
            return response()->json(['success' => true, 'message' => 'Test email sent! Check inbox + Render logs.']);
        } catch (\Exception $e) {
            \Log::error('Test email: failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    })->name('test.email');

    Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->middleware('throttle:5,1');
Route::get('/reset-password/{token}/{userId}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password/{token}/{userId}', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/pending-verification', function () {
    if (Auth::check() && Auth::user()->Is_Verified) {
        return redirect()->route('dashboard');
    }

    return view('auth.pending');
})->name('pending.verification')->middleware('auth');

Route::get('/verify-email/{token}/{userId}', [VerifyEmailController::class, 'verify'])->name('verify.email');
Route::post('/resend-verification', [VerifyEmailController::class, 'resend'])->name('resend.verification')->middleware('auth');

Route::get('/account/suspended', function () {
    return view('auth.suspended');
})->name('account.suspended')->middleware('auth');

Route::get('/account/banned', function () {
    return view('auth.banned');
})->name('account.banned')->middleware('auth');

Route::middleware(['auth', 'verified.user', 'user.active'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile/{id?}', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/{id}/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/{id}/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/{id}/adjust-picture', [ProfileController::class, 'adjustPicture'])->name('profile.adjustPicture');
    Route::post('/profile/skill/add', [ProfileController::class, 'addSkill'])->name('profile.skill.add');
    Route::post('/profile/skill/remove', [ProfileController::class, 'removeSkill'])->name('profile.skill.remove');
    Route::post('/requests/{id}/complete', [RequestController::class, 'complete'])->name('requests.complete');
    Route::post('/requests/{id}/fail', [RequestController::class, 'fail'])->name('requests.fail');
    Route::get('/requests/{id}/provider-feedback', [RequestController::class, 'providerFeedback'])->name('requests.provider-feedback');
    Route::post('/requests/{id}/provider-feedback', [RequestController::class, 'submitProviderFeedback'])->name('requests.submit-provider-feedback');
    Route::post('/requests/{id}/direct-assign', [RequestController::class, 'directAssign'])->name('requests.direct-assign');
    Route::post('/requests', [RequestController::class, 'store'])->name('requests.store')->middleware('throttle:5,1');
    Route::resource('requests', RequestController::class)->only(['index', 'show', 'edit', 'update', 'destroy']);
    Route::get('/matches', [MatchController::class, 'index'])->name('matches.index');
    Route::get('/matches/{id}', [MatchController::class, 'show'])->name('matches.show');
    Route::get('/search', [SearchController::class, 'index'])->name('search.index');
    Route::post('/search/categories', [SearchController::class, 'addCategory'])->name('search.categories.add')->middleware('admin');
    Route::get('/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
    Route::post('/assignments/apply', [AssignmentController::class, 'apply'])->name('assignments.apply');
    Route::post('/assignments/{id}/accept', [AssignmentController::class, 'accept'])->name('assignments.accept');
    Route::post('/assignments/{id}/reject', [AssignmentController::class, 'reject'])->name('assignments.reject');
    Route::post('/assignments/{id}/provider-accept', [AssignmentController::class, 'providerAccept'])->name('assignments.provider-accept');
    Route::post('/assignments/{id}/provider-reject', [AssignmentController::class, 'providerReject'])->name('assignments.provider-reject');
    Route::post('/assignments/{id}/complete', [AssignmentController::class, 'complete'])->name('assignments.complete');
    Route::post('/assignments/{id}/review', [AssignmentController::class, 'review'])->name('assignments.review');
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store')->middleware('throttle:10,1');
    Route::post('/messages/{id}/block', [BlockController::class, 'block'])->name('messages.block')->middleware('throttle:5,1');
    Route::post('/messages/{id}/unblock', [BlockController::class, 'unblock'])->name('messages.unblock')->middleware('throttle:5,1');
    Route::post('/messages/{id}/mark-unread', [MessageController::class, 'markUnread'])->name('messages.mark-unread')->middleware('throttle:5,1');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::get('/reviews/create/{id}', [ReviewController::class, 'create'])->name('reviews.create');
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::get('/reports', function () {
        $uid = Auth::id();
        $reviewsReceived = \App\Models\Review::where('Reviewed_User_ID', $uid)->with('reviewer')->latest()->get();
        $reviewsGiven = \App\Models\Review::where('Reviewer_ID', $uid)->with('reviewedUser')->latest()->get();

        return view('reports.index', compact('reviewsReceived', 'reviewsGiven'));
    })->name('reports.index');
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
    Route::get('/skills', [SkillController::class, 'index'])->name('skills.index');
    Route::post('/skills', [SkillController::class, 'store'])->name('skills.store');
    Route::post('/skills/add-to-me', [SkillController::class, 'addToMe'])->name('skills.addToMe');
Route::post('/skills/endorse', [SkillController::class, 'endorse'])->name('skills.endorse');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::get('/notifications/{id}/read', [NotificationController::class, 'readAndRedirect'])->name('notifications.read');
    Route::middleware('admin')->group(function () {
        Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('admin.analytics');
        Route::get('/analytics/report', [AnalyticsController::class, 'report'])->name('admin.analytics.report')->middleware('throttle:5,1');
        Route::post('/admin/skills', [AdminController::class, 'addSkill'])->name('admin.skills');
        Route::get('/admin/users/create', [AdminController::class, 'showCreateUserForm'])->name('admin.users.create');
        Route::post('/admin/users/register', [AdminController::class, 'registerUser'])->name('admin.users.register');
        Route::post('/admin/users/verify', [AdminController::class, 'verifyUser'])->name('admin.users.verify');
        Route::post('/admin/reports/resolve', [AdminController::class, 'resolveReport'])->name('admin.reports.resolve')->middleware('throttle:10,1');
        Route::post('/admin/users/delete', [AdminController::class, 'deleteUser'])->name('admin.users.delete')->middleware('throttle:5,1');
        Route::post('/admin/users/restore', [AdminController::class, 'restoreUser'])->name('admin.users.restore')->middleware('throttle:5,1');
    });
});
