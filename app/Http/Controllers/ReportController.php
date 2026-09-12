<?php

namespace App\Http\Controllers;

use App\Models\Contribution;
use App\Models\Fund;
use App\Models\HelpRequest;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\Member;
use App\Models\SolidarityFund;
use App\Models\Setting;
use App\Services\FundService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct(private FundService $fundService)
    {
    }

    /**
     * Applique les filtres de date basés sur la requête.
     */
    private function applyDateFilter($query, Request $request, string $column = 'created_at')
    {
        if ($request->get('period') === 'all') {
            return $query;
        }

        $from = $request->get('from');
        $to   = $request->get('to');

        if ($from) {
            $query->whereDate($column, '>=', $from);
        }
        if ($to) {
            $query->whereDate($column, '<=', $to);
        }

        return $query;
    }

    private function getPeriodLabel(Request $request): string
    {
        if ($request->get('period') === 'all') {
            return 'Toute la période (sans exception)';
        }
        $from = $request->get('from') ? Carbon::parse($request->get('from'))->format('d/m/Y') : '...';
        $to   = $request->get('to') ? Carbon::parse($request->get('to'))->format('d/m/Y') : '...';
        return "Période du {$from} au {$to}";
    }

    // ── Rapport Général ─────────────────────────────────────────────────────
    public function general(Request $request)
    {
        // 1. Audit de Liquidité
        $grossBalance = $this->fundService->getGrossBalance();
        $activeLoansEncumbrance = (float) Loan::where('status', 'active')->sum('balance_remaining');
        $loanMargin = (float) Setting::get('loan_fund_margin', 20);
        $helpMargin = (float) Setting::get('help_fund_margin', 10);
        $solidarityBalance = SolidarityFund::currentBalance();

        // 2. Membres & Performance
        $members = Member::withCount(['contributions' => fn($q) => $q->where('status', 'paid')])
            ->withSum(['contributions' => fn($q) => $q->where('status', 'paid')], 'amount')
            ->orderBy('member_number', 'asc')
            ->get();

        // 3. Cotisations (filtrées)
        $contributions = $this->applyDateFilter(Contribution::with('member'), $request, 'payment_date')
            ->orderBy('payment_date', 'desc')
            ->get();

        // 4. Prêts (filtrés)
        $loans = $this->applyDateFilter(Loan::with('member'), $request)
            ->orderBy('created_at', 'desc')
            ->get();

        // 5. Remboursements (filtrés)
        $repayments = $this->applyDateFilter(LoanRepayment::with(['loan.member']), $request, 'payment_date')
            ->orderBy('payment_date', 'desc')
            ->get();

        // 6. Aides Sociales (filtrées)
        $helpRequests = $this->applyDateFilter(HelpRequest::with('member'), $request)
            ->orderBy('created_at', 'desc')
            ->get();

        // 7. Dépenses (filtrées)
        $expenses = $this->applyDateFilter(Fund::where('type', 'outflow')->whereNull('reference_type'), $request)
            ->orderBy('created_at', 'desc')
            ->get();

        $data = [
            'title'            => 'Rapport Général Consolidé',
            'date'             => now()->format('d/m/Y H:i'),
            'period_label'     => $this->getPeriodLabel($request),
            
            // Stats Sommaires
            'balance'          => $grossBalance,
            'available'        => $this->fundService->getAvailableBalance(),
            'solidarity'       => $solidarityBalance,
            'total_loaned'     => $activeLoansEncumbrance,
            'total_members'    => Member::count(),
            'active_members'   => Member::where('status', 'active')->count(),
            'suspended'        => Member::where('status', 'suspended')->count(),
            'total_contributions' => Contribution::where('status', 'paid')->sum('amount'),
            'pending_loans'    => Loan::where('status', 'pending')->count(),
            'active_loans'     => Loan::where('status', 'active')->count(),
            'pending_help'     => HelpRequest::where('status', 'pending')->count(),
            'late_contributions' => Contribution::where('status', 'late')->count(),

            // Données détaillées pour chaque section
            'audit' => [
                'gross_balance'     => $grossBalance,
                'loans_encumbrance' => $activeLoansEncumbrance,
                'available_raw'     => $grossBalance - $activeLoansEncumbrance,
                'loan_margin'       => $loanMargin,
                'help_margin'       => $helpMargin,
                'solidarity'        => $solidarityBalance,
            ],
            'members'        => $members,
            'contributions'  => $contributions,
            'loans'          => $loans,
            'repayments'     => $repayments,
            'helpRequests'   => $helpRequests,
            'expenses'       => $expenses,
            'total_expenses' => $expenses->sum('amount'),
        ];

        $pdf = Pdf::loadView('reports.general', $data)->setPaper('A4', 'landscape');
        return $this->returnPdf($pdf, 'rapport-general-complet', $request);
    }


    // ── Rapport des Membres [NOUVEAU] ───────────────────────────────────────
    public function members(Request $request)
    {
        $members = Member::withCount(['contributions' => fn($q) => $q->where('status', 'paid')])
            ->withSum(['contributions' => fn($q) => $q->where('status', 'paid')], 'amount')
            ->orderBy('joined_at', 'desc')
            ->get();

        $pdf = Pdf::loadView('reports.members', [
            'title'        => 'Rapport des Membres',
            'date'         => now()->format('d/m/Y H:i'),
            'period_label' => $this->getPeriodLabel($request),
            'members'      => $members,
        ])->setPaper('A4', 'landscape');

        return $this->returnPdf($pdf, 'rapport-membres', $request);
    }

    // ── Rapport des Dépenses [NOUVEAU] ──────────────────────────────────────
    public function expenses(Request $request)
    {
        // On considère comme dépenses les sorties qui ne sont ni des prêts ni des aides identifiées
        $query = Fund::where('type', 'outflow')
            ->whereNull('reference_type'); // Sorties directes / Dépenses de fonctionnement

        $expenses = $this->applyDateFilter($query, $request)->get();

        $pdf = Pdf::loadView('reports.expenses', [
            'title'        => 'Journal des Dépenses & Sorties',
            'date'         => now()->format('d/m/Y H:i'),
            'period_label' => $this->getPeriodLabel($request),
            'expenses'     => $expenses,
            'total'        => $expenses->sum('amount'),
        ])->setPaper('A4', 'portrait');

        return $this->returnPdf($pdf, 'rapport-depenses', $request);
    }

    // ── Audit Caisse & Solde [NOUVEAU] ──────────────────────────────────────
    public function balance(Request $request)
    {
        $grossBalance = $this->fundService->getGrossBalance();
        $activeLoansEncumbrance = (float) Loan::where('status', 'active')->sum('balance_remaining');
        
        $loanMargin = (float) Setting::get('loan_fund_margin', 20);
        $helpMargin = (float) Setting::get('help_fund_margin', 10);

        $pdf = Pdf::loadView('reports.available_balance', [
            'title'            => 'Audit du Solde Disponible',
            'date'             => now()->format('d/m/Y H:i'),
            'gross_balance'    => $grossBalance,
            'loans_encumbrance'=> $activeLoansEncumbrance,
            'available_raw'    => $grossBalance - $activeLoansEncumbrance,
            'loan_margin'      => $loanMargin,
            'help_margin'      => $helpMargin,
            'solidarity'       => SolidarityFund::currentBalance(),
        ])->setPaper('A4', 'portrait');

        return $this->returnPdf($pdf, 'audit-solde', $request);
    }

    // ── Rapport Cotisations ─────────────────────────────────────────────────
    public function contributions(Request $request)
    {
        $query = Contribution::with('member');
        $contributions = $this->applyDateFilter($query, $request, 'payment_date')
            ->orderBy('payment_date', 'desc')
            ->get();

        $pdf = Pdf::loadView('reports.contributions', [
            'title'         => 'Rapport des Cotisations',
            'date'          => now()->format('d/m/Y H:i'),
            'period_label'  => $this->getPeriodLabel($request),
            'contributions' => $contributions,
            'total'         => $contributions->where('status', 'paid')->sum('amount'),
            'late_count'    => $contributions->where('status', 'late')->count(),
        ])->setPaper('A4', 'landscape');

        return $this->returnPdf($pdf, 'rapport-cotisations', $request);
    }

    // ── Rapport Prêts ──────────────────────────────────────────────────────
    public function loans(Request $request)
    {
        $query = Loan::with('member');
        $loans = $this->applyDateFilter($query, $request)->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('reports.loans', [
            'title'            => 'Rapport des Prêts',
            'date'             => now()->format('d/m/Y H:i'),
            'period_label'     => $this->getPeriodLabel($request),
            'loans'            => $loans,
            'total_principal'  => $loans->sum('principal_amount'),
            'total_remaining'  => $loans->where('status', 'active')->sum('balance_remaining'),
            'pending_count'    => $loans->where('status', 'pending')->count(),
            'active_count'     => $loans->where('status', 'active')->count(),
            'repaid_count'     => $loans->where('status', 'repaid')->count(),
        ])->setPaper('A4', 'landscape');

        return $this->returnPdf($pdf, 'rapport-prets', $request);
    }

    // ── Rapport Remboursements ─────────────────────────────────────────────
    public function repayments(Request $request)
    {
        $query = LoanRepayment::with(['loan.member']);
        $repayments = $this->applyDateFilter($query, $request, 'payment_date')
            ->orderBy('payment_date', 'desc')
            ->get();

        $pdf = Pdf::loadView('reports.repayments', [
            'title'        => 'Rapport des Remboursements',
            'date'         => now()->format('d/m/Y H:i'),
            'period_label' => $this->getPeriodLabel($request),
            'repayments'   => $repayments,
            'total'        => $repayments->sum('amount'),
        ])->setPaper('A4', 'landscape');

        return $this->returnPdf($pdf, 'rapport-remboursements', $request);
    }

    // ── Rapport Demandes d'Aide ────────────────────────────────────────────
    public function helpRequests(Request $request)
    {
        $query = HelpRequest::with('member');
        $helpRequests = $this->applyDateFilter($query, $request)->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('reports.help_requests', [
            'title'          => 'Rapport des Demandes d\'Aide',
            'date'           => now()->format('d/m/Y H:i'),
            'period_label'   => $this->getPeriodLabel($request),
            'helpRequests'   => $helpRequests,
            'total_approved' => $helpRequests->where('status', 'paid')->sum('amount_requested'),
            'pending_count'  => $helpRequests->where('status', 'pending')->count(),
        ])->setPaper('A4', 'landscape');

        return $this->returnPdf($pdf, 'rapport-aides', $request);
    }

    // ── Rapport Complet d'un Membre (sans exception) ────────────────────────
    public function member(Request $request, Member $member)
    {
        $contributions = $member->contributions()->orderByDesc('payment_date')->get();
        $loans         = $member->loans()->with('schedules')->orderByDesc('created_at')->get();
        $repayments    = $member->loanRepayments()->orderByDesc('payment_date')->get();
        $helpRequests  = $member->helpRequests()->orderByDesc('created_at')->get();
        $solidarity    = $member->solidarityMovements()->orderByDesc('created_at')->get();
        $contribPenalties = $member->contributionPenalties()->orderByDesc('created_at')->get();
        $loanPenalties    = $member->loanPenalties()->orderByDesc('created_at')->get();

        $pdf = Pdf::loadView('reports.member', [
            'title'    => "Rapport Complet — {$member->full_name}",
            'date'     => now()->format('d/m/Y H:i'),
            'currency' => Setting::get('currency', 'Gourdes'),
            'member'   => $member,

            'contributions'      => $contributions,
            'totalContributions' => $contributions->where('status', 'paid')->sum('amount'),

            'loans' => $loans,

            'repayments'      => $repayments,
            'totalRepayments' => $repayments->sum('amount_paid'),

            'helpRequests'  => $helpRequests,
            'totalHelpPaid' => $helpRequests->where('status', 'paid')->sum('amount_requested'),

            'solidarity'      => $solidarity,
            'totalSolidarity' => $solidarity->where('type', 'inflow')->sum('amount'),

            'contribPenalties' => $contribPenalties,
            'loanPenalties'    => $loanPenalties,
        ])->setPaper('A4', 'portrait');

        return $this->returnPdf($pdf, "rapport-membre-{$member->member_number}", $request);
    }

    private function returnPdf($pdf, string $name, Request $request)
    {
        $filename = "{$name}-" . now()->format('Y-m-d') . '.pdf';

        if ($request->get('action') === 'download') {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }
}
