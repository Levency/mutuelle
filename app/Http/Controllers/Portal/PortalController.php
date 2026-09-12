<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Contribution;
use App\Models\HelpRequest;
use App\Models\Loan;
use App\Models\Member;
use App\Models\Setting;
use App\Models\SolidarityFund;
use App\Services\FundService;
use Illuminate\Support\Facades\Auth;

/**
 * Espace membre en lecture seule. Chaque action est systématiquement bornée
 * au membre authentifié (guard "member") — jamais de paramètre d'identifiant
 * de membre pris depuis la requête, pour exclure toute fuite vers les
 * dossiers d'un autre membre.
 */
class PortalController extends Controller
{
    private function member(): Member
    {
        return Auth::guard('member')->user();
    }

    public function dashboard()
    {
        $member = $this->member();

        $totalContributed = (float) $member->contributions()->where('status', 'paid')->sum('amount');
        $activeLoan       = $member->loans()->where('status', 'active')->latest()->first();
        $pendingHelp      = $member->helpRequests()->where('status', 'pending')->count();
        $lastContribution = $member->contributions()->latest('payment_date')->first();
        $latePenalties    = $member->contributionPenalties()->where('status', 'pending')->sum('amount')
            + $member->loanPenalties()->where('status', 'pending')->sum('amount');

        return view('portal.dashboard', [
            'member'            => $member,
            'currency'          => Setting::get('currency', 'Gourdes'),
            'totalContributed'  => $totalContributed,
            'activeLoan'        => $activeLoan,
            'pendingHelp'       => $pendingHelp,
            'lastContribution'  => $lastContribution,
            'latePenalties'     => $latePenalties,
        ]);
    }

    public function contributions()
    {
        $member = $this->member();

        $contributions = $member->contributions()
            ->orderByDesc('payment_date')
            ->paginate(15);

        $penalties = $member->contributionPenalties()
            ->orderByDesc('created_at')
            ->get();

        return view('portal.contributions', [
            'currency'      => Setting::get('currency', 'Gourdes'),
            'contributions' => $contributions,
            'penalties'     => $penalties,
            'total'         => $member->contributions()->where('status', 'paid')->sum('amount'),
        ]);
    }

    public function loans()
    {
        $member = $this->member();

        $loans = $member->loans()->orderByDesc('created_at')->get();

        return view('portal.loans.index', [
            'currency' => Setting::get('currency', 'Gourdes'),
            'loans'    => $loans,
        ]);
    }

    public function loanShow(int $loan)
    {
        // Portée strictement au membre connecté : un prêt d'un autre membre
        // renvoie un 404, sans révéler qu'il existe.
        $loan = $this->member()->loans()
            ->with(['schedules' => fn ($q) => $q->orderBy('due_date'), 'repayments' => fn ($q) => $q->orderByDesc('payment_date')])
            ->findOrFail($loan);

        return view('portal.loans.show', [
            'currency' => Setting::get('currency', 'Gourdes'),
            'loan'     => $loan,
        ]);
    }

    public function help()
    {
        $member = $this->member();

        $helpRequests = $member->helpRequests()
            ->with('validations')
            ->orderByDesc('created_at')
            ->get();

        return view('portal.help', [
            'currency'     => Setting::get('currency', 'Gourdes'),
            'helpRequests' => $helpRequests,
        ]);
    }

    public function solidarity()
    {
        $member = $this->member();

        $contributionIds = $member->contributions()->pluck('id');

        $movements = SolidarityFund::where('reference_type', Contribution::class)
            ->whereIn('reference_id', $contributionIds)
            ->orderByDesc('created_at')
            ->get();

        return view('portal.solidarity', [
            'currency'    => Setting::get('currency', 'Gourdes'),
            'movements'   => $movements,
            'myTotal'     => $movements->sum('amount'),
            'fundBalance' => SolidarityFund::currentBalance(),
        ]);
    }

    public function report(FundService $fundService)
    {
        // Rapport général : uniquement des agrégats de la mutuelle dans son
        // ensemble — aucune donnée nominative sur les autres membres.
        return view('portal.report', [
            'currency'           => Setting::get('currency', 'Gourdes'),
            'grossBalance'       => $fundService->getGrossBalance(),
            'availableBalance'   => $fundService->getAvailableBalance(),
            'activeLoansTotal'   => Loan::where('status', 'active')->sum('balance_remaining'),
            'solidarityBalance'  => SolidarityFund::currentBalance(),
            'totalMembers'       => Member::where('status', 'active')->count(),
            'totalContributions' => Contribution::where('status', 'paid')->sum('amount'),
            'activeLoansCount'   => Loan::where('status', 'active')->count(),
            'pendingHelpCount'   => HelpRequest::where('status', 'pending')->count(),
        ]);
    }
}
