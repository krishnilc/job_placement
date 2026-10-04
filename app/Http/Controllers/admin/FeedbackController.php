<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    /**
     * List all feedback submitted by employers and students. Restricted to
     * admin/super_admin via the "checkAdminOrSuperAdmin" route middleware.
     */
    public function index(Request $request)
    {
        $typeFilter = $request->query('type');
        $search = trim((string) $request->query('search', ''));

        $feedbacks = Feedback::query()
            ->with([
                'givenBy:id,name,email,role',
                'givenTo:id,name,email,role',
                'jobApplication.job:id,title,company_name',
            ])
            ->when($typeFilter, fn ($query) => $query->where('feedback_type', $typeFilter))
            ->when($search !== '', function ($query) use ($search) {
                $like = '%' . $search . '%';
                $query->where(function ($q) use ($like) {
                    $q->whereHas('givenBy', fn ($u) => $u->where('name', 'like', $like))
                        ->orWhereHas('givenTo', fn ($u) => $u->where('name', 'like', $like))
                        ->orWhereHas('jobApplication.job', fn ($j) => $j->where('title', 'like', $like)->orWhere('company_name', 'like', $like));
                });
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.feedback.list', [
            'feedbacks' => $feedbacks,
            'typeFilter' => $typeFilter,
        ]);
    }
}
