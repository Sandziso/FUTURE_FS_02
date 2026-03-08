<?php
// admin/views/reports.php
require_once '../includes/header.php';

// Default date range: last 30 days
$end_date = date('Y-m-d');
$start_date = date('Y-m-d', strtotime('-30 days'));

if (isset($_GET['filter'])) {
    $start_date = $_GET['start_date'] ?? $start_date;
    $end_date = $_GET['end_date'] ?? $end_date;
}

// Fetch data
$totalLeads = $pdo->query("SELECT COUNT(*) FROM leads WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'")->fetchColumn();
$convertedLeads = $pdo->query("SELECT COUNT(*) FROM leads WHERE status='converted' AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'")->fetchColumn();
$totalClients = $pdo->query("SELECT COUNT(*) FROM clients WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'")->fetchColumn();
$pipelineValue = $pdo->query("SELECT SUM(estimated_value) FROM leads WHERE status != 'converted' AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'")->fetchColumn() ?: 0;

// Leads by source
$sourceStats = $pdo->prepare("SELECT source, COUNT(*) as count FROM leads WHERE source IS NOT NULL AND DATE(created_at) BETWEEN ? AND ? GROUP BY source ORDER BY count DESC");
$sourceStats->execute([$start_date, $end_date]);
$sourceStats = $sourceStats->fetchAll();

// Daily trend
$trend = $pdo->prepare("SELECT DATE(created_at) as day, COUNT(*) as count FROM leads WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY DATE(created_at) ORDER BY day");
$trend->execute([$start_date, $end_date]);
$trendData = $trend->fetchAll();
$trendLabels = [];
$trendCounts = [];
foreach ($trendData as $row) {
    $trendLabels[] = date('d M', strtotime($row['day']));
    $trendCounts[] = $row['count'];
}

// Status breakdown
$statusStats = $pdo->prepare("SELECT status, COUNT(*) as count FROM leads WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY status");
$statusStats->execute([$start_date, $end_date]);
$statusStats = $statusStats->fetchAll();
$statusLabels = [];
$statusCounts = [];
foreach ($statusStats as $row) {
    $statusLabels[] = ucfirst($row['status']);
    $statusCounts[] = $row['count'];
}
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Reports</h1>
    <div>
        <button class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
        <a href="export_reports.php?start=<?php echo $start_date; ?>&end=<?php echo $end_date; ?>" class="btn btn-sm btn-success"><i class="bi bi-download"></i> Export</a>
    </div>
</div>

<!-- Date Filter Card -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
            </div>
            <div class="col-md-4">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" name="filter" class="btn btn-primary"><i class="bi bi-funnel"></i> Apply Filter</button>
                <a href="reports.php" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body"><div class="row no-gutters align-items-center">
                <div class="col mr-2"><div class="text-xs font-weight-bold text-primary text-uppercase mb-1">New Leads</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalLeads; ?></div></div>
                <div class="col-auto"><i class="bi bi-people fa-2x text-gray-300"></i></div>
            </div></div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body"><div class="row no-gutters align-items-center">
                <div class="col mr-2"><div class="text-xs font-weight-bold text-success text-uppercase mb-1">Conversions</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $convertedLeads; ?></div></div>
                <div class="col-auto"><i class="bi bi-check-circle fa-2x text-gray-300"></i></div>
            </div></div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body"><div class="row no-gutters align-items-center">
                <div class="col mr-2"><div class="text-xs font-weight-bold text-info text-uppercase mb-1">New Clients</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalClients; ?></div></div>
                <div class="col-auto"><i class="bi bi-building fa-2x text-gray-300"></i></div>
            </div></div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body"><div class="row no-gutters align-items-center">
                <div class="col mr-2"><div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pipeline Value</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo formatCurrency($pipelineValue); ?></div></div>
                <div class="col-auto"><i class="bi bi-cash-stack fa-2x text-gray-300"></i></div>
            </div></div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card shadow">
            <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Daily Lead Trend</h6></div>
            <div class="card-body"><canvas id="trendChart"></canvas></div>
        </div>
    </div>
    <div class="col-lg-4 mb-4">
        <div class="card shadow">
            <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Lead Status</h6></div>
            <div class="card-body"><canvas id="statusChart"></canvas></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Top Sources</h6></div>
            <div class="card-body"><canvas id="sourceChart"></canvas></div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Conversion Funnel</h6></div>
            <div class="card-body">
                <div class="mb-3">
                    <label>New → Contacted: <?php echo $totalLeads ? round(($statusStats[1]['count']??0)/$totalLeads*100,1) : 0; ?>%</label>
                    <div class="progress"><div class="progress-bar bg-info" style="width: <?php echo $totalLeads ? round(($statusStats[1]['count']??0)/$totalLeads*100,1) : 0; ?>%"></div></div>
                </div>
                <div class="mb-3">
                    <label>Contacted → Converted: <?php echo ($statusStats[1]['count']??0) ? round(($convertedLeads)/($statusStats[1]['count']??1)*100,1) : 0; ?>%</label>
                    <div class="progress"><div class="progress-bar bg-success" style="width: <?php echo ($statusStats[1]['count']??0) ? round(($convertedLeads)/($statusStats[1]['count']??1)*100,1) : 0; ?>%"></div></div>
                </div>
                <div class="mb-3">
                    <label>Overall Conversion: <?php echo $totalLeads ? round($convertedLeads/$totalLeads*100,1) : 0; ?>%</label>
                    <div class="progress"><div class="progress-bar bg-primary" style="width: <?php echo $totalLeads ? round($convertedLeads/$totalLeads*100,1) : 0; ?>%"></div></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Trend Chart
    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode($trendLabels); ?>,
            datasets: [{
                label: 'Leads',
                data: <?php echo json_encode($trendCounts); ?>,
                borderColor: '#4361ee',
                backgroundColor: 'rgba(67, 97, 238, 0.05)',
                tension: 0.3,
                fill: true
            }]
        },
        options: { scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });

    // Status Pie Chart
    new Chart(document.getElementById('statusChart'), {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($statusLabels); ?>,
            datasets: [{
                data: <?php echo json_encode($statusCounts); ?>,
                backgroundColor: ['#f6c23e', '#36b9cc', '#1cc88a']
            }]
        }
    });

    // Source Pie Chart
    new Chart(document.getElementById('sourceChart'), {
        type: 'pie',
        data: {
            labels: <?php echo json_encode(array_column($sourceStats, 'source')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($sourceStats, 'count')); ?>,
                backgroundColor: ['#f6c23e', '#36b9cc', '#1cc88a', '#e74a3b', '#858796']
            }]
        }
    });
</script>

<?php include '../includes/footer.php'; ?>