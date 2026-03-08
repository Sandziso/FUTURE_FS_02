<?php
// admin/dashboard.php
require_once 'includes/header.php';

// --- Lead statistics (original) ---
$totalLeads   = $pdo->query("SELECT COUNT(*) FROM leads")->fetchColumn();
$converted    = $pdo->query("SELECT COUNT(*) FROM leads WHERE status = 'converted'")->fetchColumn();
$newLeads     = $pdo->query("SELECT COUNT(*) FROM leads WHERE status = 'new'")->fetchColumn();
$contacted    = $pdo->query("SELECT COUNT(*) FROM leads WHERE status = 'contacted'")->fetchColumn();

$pipelineValue = $pdo->query("SELECT SUM(estimated_value) FROM leads WHERE status != 'converted'")->fetchColumn();
$pipelineValue = $pipelineValue ?: 0;

$conversionRate = ($totalLeads > 0) ? round(($converted / $totalLeads) * 100, 1) : 0;

$sourceStats = $pdo->query("
    SELECT source, COUNT(*) as count 
    FROM leads 
    WHERE source IS NOT NULL 
    GROUP BY source 
    ORDER BY count DESC 
    LIMIT 5
")->fetchAll();

$recentActivity = $pdo->query("
    SELECT al.*, u.username 
    FROM activity_log al
    JOIN users u ON al.user_id = u.id
    ORDER BY al.created_at DESC
    LIMIT 10
")->fetchAll();

$recentLeads = $pdo->query("
    SELECT id, name, email, status, estimated_value, created_at 
    FROM leads 
    ORDER BY created_at DESC 
    LIMIT 5
")->fetchAll();

$trendData = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM leads WHERE DATE(created_at) = ?");
    $stmt->execute([$date]);
    $trendData['labels'][] = date('D', strtotime($date));
    $trendData['counts'][] = (int)$stmt->fetchColumn();
}

// --- New: Project statistics ---
$totalProjects = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$activeProjects = $pdo->query("SELECT COUNT(*) FROM projects WHERE status = 'active'")->fetchColumn();
$completedProjects = $pdo->query("SELECT COUNT(*) FROM projects WHERE status = 'completed'")->fetchColumn();
$totalProjectBudget = $pdo->query("SELECT SUM(estimated_budget) FROM projects")->fetchColumn() ?: 0;

// Recent projects
$recentProjects = $pdo->query("
    SELECT p.id, p.name, p.status, p.estimated_budget, c.company_name as client
    FROM projects p
    LEFT JOIN clients c ON p.client_id = c.id
    ORDER BY p.created_at DESC
    LIMIT 5
")->fetchAll();

// --- New: Invoice statistics ---
$totalInvoices = $pdo->query("SELECT COUNT(*) FROM invoices")->fetchColumn();
$paidInvoices = $pdo->query("SELECT COUNT(*) FROM invoices WHERE status = 'paid'")->fetchColumn();
$overdueInvoices = $pdo->query("
    SELECT COUNT(*) FROM invoices 
    WHERE status = 'overdue' OR (status = 'sent' AND due_date < CURDATE())
")->fetchColumn();
$totalRevenue = $pdo->query("SELECT SUM(total_amount) FROM invoices WHERE status = 'paid'")->fetchColumn() ?: 0;
$outstandingRevenue = $pdo->query("
    SELECT SUM(total_amount) FROM invoices 
    WHERE status IN ('sent', 'overdue') OR (status = 'draft')
")->fetchColumn() ?: 0;   // could also include draft if desired

// Recent invoices
$recentInvoices = $pdo->query("
    SELECT i.id, i.invoice_no, i.total_amount, i.status, i.due_date, c.company_name as client
    FROM invoices i
    LEFT JOIN clients c ON i.client_id = c.id
    ORDER BY i.created_at DESC
    LIMIT 5
")->fetchAll();
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
    <div>
        <span class="text-muted small me-2" id="last-updated">Updated just now</span>
        <button class="btn btn-sm btn-outline-secondary" onclick="refreshDashboard()" id="refreshBtn">
            <i class="bi bi-arrow-repeat"></i> Refresh
        </button>
        <a href="export_leads.php" class="btn btn-sm btn-success ms-2">
            <i class="bi bi-file-earmark-excel"></i> Export Leads
        </a>
    </div>
</div>

<!-- Key Lead Stats Row (original) -->
<div class="row" id="stats-cards">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Leads</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-leads"><?php echo $totalLeads; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-people-fill fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Conversion Rate</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="conversion-rate"><?php echo $conversionRate; ?>%</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-graph-up-arrow fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Pipeline Value</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo formatCurrency($pipelineValue); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-cash-stack fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">New Leads (7d)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="new-leads-7d"><?php echo array_sum($trendData['counts']); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-star-fill fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row (original) -->
<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Lead Trend (Last 7 Days)</h6>
            </div>
            <div class="card-body">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Lead Sources</h6>
            </div>
            <div class="card-body">
                <canvas id="sourceChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Lead Status Summary (original) -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-light border-0 text-center h-100">
            <div class="card-body">
                <div class="display-4 text-primary mb-2">🆕</div>
                <h5 class="card-title text-primary">New</h5>
                <h2 class="fw-bold" id="new-count"><?php echo $newLeads; ?></h2>
                <p class="text-muted small">Awaiting first contact</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-light border-0 text-center h-100">
            <div class="card-body">
                <div class="display-4 text-warning mb-2">📞</div>
                <h5 class="card-title text-warning">Contacted</h5>
                <h2 class="fw-bold" id="contacted-count"><?php echo $contacted; ?></h2>
                <p class="text-muted small">Follow‑up needed</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-light border-0 text-center h-100">
            <div class="card-body">
                <div class="display-4 text-success mb-2">✅</div>
                <h5 class="card-title text-success">Converted</h5>
                <h2 class="fw-bold" id="converted-count"><?php echo $converted; ?></h2>
                <p class="text-muted small">Became clients</p>
            </div>
        </div>
    </div>
</div>

<!-- NEW: Project Stats Row -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Projects</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalProjects; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-kanban fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Projects</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $activeProjects; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-play-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Completed</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $completedProjects; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Budget</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo formatCurrency($totalProjectBudget); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-calculator fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- NEW: Invoice Stats Row -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Invoices</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalInvoices; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-receipt fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Paid</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $paidInvoices; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-check-circle-fill fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Overdue</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $overdueInvoices; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Revenue (Paid)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo formatCurrency($totalRevenue); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-cash-coin fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Row: Conversion Metrics & Source Performance -->
<div class="row">
    <div class="col-xl-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Lead Source Performance</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead><tr><th>Source</th><th>Total Leads</th><th>Converted</th><th>Rate</th></tr></thead>
                    <tbody>
                        <?php 
                        $sourcePerf = sourcePerformance();
                        foreach ($sourcePerf as $src): 
                            $rate = $src['total'] > 0 ? round(($src['converted']/$src['total'])*100,1) : 0;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($src['source']); ?></td>
                            <td><?php echo $src['total']; ?></td>
                            <td><?php echo $src['converted']; ?></td>
                            <td><?php echo $rate; ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Conversion Insights</h6>
            </div>
            <div class="card-body">
                <p>Average time to convert: <strong><?php echo averageConversionDays(); ?> days</strong></p>
                <p>Overdue tasks (all users): <strong><?php echo countOverdueTasks(); ?></strong></p>
                <p>Currency: <?php echo getSetting('currency', 'ZAR'); ?></p>
                <p>Company: <?php echo htmlspecialchars(getSetting('company_name', 'LeadFlow')); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- NEW: Recent Projects & Invoices Row (two columns) -->
<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Recent Projects</h6>
                <a href="<?php echo BASE_URL; ?>/views/projects.php" class="btn btn-primary btn-sm">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Project</th>
                                <th>Client</th>
                                <th>Status</th>
                                <th>Budget</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentProjects as $project): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($project['name']); ?></td>
                                <td><?php echo htmlspecialchars($project['client'] ?: '-'); ?></td>
                                <td>
                                    <?php
                                    $status_colors = [
                                        'planning' => 'secondary',
                                        'active' => 'primary',
                                        'on_hold' => 'warning',
                                        'completed' => 'success',
                                        'cancelled' => 'danger'
                                    ];
                                    $color = $status_colors[$project['status']] ?? 'secondary';
                                    echo "<span class=\"badge bg-{$color}\">" . ucfirst($project['status']) . "</span>";
                                    ?>
                                </td>
                                <td><?php echo formatCurrency($project['estimated_budget']); ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/views/project_details.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Recent Invoices</h6>
                <a href="<?php echo BASE_URL; ?>/views/invoices.php" class="btn btn-primary btn-sm">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Client</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Due Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentInvoices as $invoice): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($invoice['invoice_no']); ?></td>
                                <td><?php echo htmlspecialchars($invoice['client'] ?: '-'); ?></td>
                                <td><?php echo formatCurrency($invoice['total_amount']); ?></td>
                                <td>
                                    <?php
                                    $status_colors = [
                                        'draft' => 'secondary',
                                        'sent' => 'primary',
                                        'paid' => 'success',
                                        'overdue' => 'danger',
                                        'cancelled' => 'secondary'
                                    ];
                                    $color = $status_colors[$invoice['status']] ?? 'secondary';
                                    echo "<span class=\"badge bg-{$color}\">" . ucfirst($invoice['status']) . "</span>";
                                    ?>
                                </td>
                                <td><?php echo formatDate($invoice['due_date'], 'd M Y'); ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/views/invoice_details.php?id=<?php echo $invoice['id']; ?>" class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Original Recent Leads & Activity Row -->
<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Recent Leads</h6>
                <div>
                    <a href="<?php echo BASE_URL; ?>/views/leads.php" class="btn btn-primary btn-sm">View All</a>
                    <a href="<?php echo BASE_URL; ?>/views/add_lead.php" class="btn btn-success btn-sm">Add New</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Value</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="recent-leads-body">
                            <?php foreach ($recentLeads as $lead): ?>
                            <tr>
                                <td data-label="Name"><?php echo htmlspecialchars($lead['name']); ?></td>
                                <td data-label="Value"><?php echo formatCurrency($lead['estimated_value']); ?></td>
                                <td data-label="Status"><?php echo statusBadge($lead['status']); ?></td>
                                <td data-label="Action">
                                    <a href="<?php echo BASE_URL; ?>/views/lead_details.php?id=<?php echo $lead['id']; ?>" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush" id="activity-log">
                    <?php if (empty($recentActivity)): ?>
                        <div class="list-group-item text-muted">No recent activity</div>
                    <?php else: ?>
                        <?php foreach ($recentActivity as $log): ?>
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1"><?php echo htmlspecialchars($log['action']); ?></h6>
                                <small class="text-muted" data-timestamp="<?php echo $log['created_at']; ?>"><?php echo timeAgo($log['created_at']); ?></small>
                            </div>
                            <p class="mb-1 text-muted small"><?php echo htmlspecialchars($log['description']); ?></p>
                            <small>by <?php echo htmlspecialchars($log['username']); ?></small>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading overlay (used during refresh) -->
<div class="spinner-overlay" id="loadingOverlay">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

<!-- Chart.js Initialization -->
<script>
    // Trend chart
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    let trendChart = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($trendData['labels']); ?>,
            datasets: [{
                label: 'Leads',
                data: <?php echo json_encode($trendData['counts']); ?>,
                borderColor: '#4361ee',
                backgroundColor: 'rgba(67, 97, 238, 0.05)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } } },
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Source pie chart
    const sourceCtx = document.getElementById('sourceChart').getContext('2d');
    new Chart(sourceCtx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode(array_column($sourceStats, 'source')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($sourceStats, 'count')); ?>,
                backgroundColor: ['#f6c23e', '#36b9cc', '#1cc88a', '#e74a3b', '#858796'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // Real-time refresh (updates only lead-related sections)
    async function refreshDashboard() {
        const btn = document.getElementById('refreshBtn');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
        btn.disabled = true;

        const overlay = document.getElementById('loadingOverlay');
        overlay.classList.add('show');

        try {
            const response = await fetch('<?php echo BASE_URL; ?>/api/dashboard_stats.php');
            const data = await response.json();

            // Update lead stats
            document.getElementById('total-leads').innerText = data.total;
            document.getElementById('conversion-rate').innerText = data.conversion_rate + '%';
            document.getElementById('new-count').innerText = data.new;
            document.getElementById('contacted-count').innerText = data.contacted;
            document.getElementById('converted-count').innerText = data.converted;
            document.getElementById('new-leads-7d').innerText = data.trend.counts.reduce((a,b) => a + b, 0);

            // Update trend chart
            trendChart.data.labels = data.trend.labels;
            trendChart.data.datasets[0].data = data.trend.counts;
            trendChart.update();

            // Update recent leads table
            let recentHtml = '';
            data.recent.forEach(lead => {
                recentHtml += `<tr>
                    <td data-label="Name">${escapeHtml(lead.name)}</td>
                    <td data-label="Value">${lead.estimated_value ? 'R ' + parseFloat(lead.estimated_value).toFixed(2) : '-'}</td>
                    <td data-label="Status">${lead.status_badge}</td>
                    <td data-label="Action"><a href="<?php echo BASE_URL; ?>/views/lead_details.php?id=${lead.id}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td>
                </tr>`;
            });
            document.getElementById('recent-leads-body').innerHTML = recentHtml;

            // Update activity log if present
            if (data.activity) {
                let activityHtml = '';
                data.activity.forEach(log => {
                    activityHtml += `<div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">${escapeHtml(log.action)}</h6>
                            <small class="text-muted">${log.time_ago}</small>
                        </div>
                        <p class="mb-1 text-muted small">${escapeHtml(log.description)}</p>
                        <small>by ${escapeHtml(log.username)}</small>
                    </div>`;
                });
                document.getElementById('activity-log').innerHTML = activityHtml;
            }

            document.getElementById('last-updated').innerText = 'Updated ' + new Date().toLocaleTimeString();
        } catch (error) {
            console.error('Refresh failed:', error);
            document.getElementById('last-updated').innerText = 'Update failed';
        } finally {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
            overlay.classList.remove('show');
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Auto-refresh every 60 seconds
    setInterval(refreshDashboard, 60000);
</script>

<?php include 'includes/footer.php'; ?>