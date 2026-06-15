<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}




include 'db.php';

/** ------------------ USERS (line chart by month + role) ------------------ */
$stats = [
    "users" => array_fill(1, 12, 0),
    "companies" => array_fill(1, 12, 0),
    "admins" => array_fill(1, 12, 0)
];

$sql = "SELECT role, MONTH(created_at) as month, COUNT(*) as total FROM users GROUP BY role, MONTH(created_at)";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    if ($row['role'] === 'user') {
        $stats['users'][$row['month']] = $row['total'];
    } elseif ($row['role'] === 'company') {
        $stats['companies'][$row['month']] = $row['total'];
    } elseif ($row['role'] === 'admin') {
        $stats['admins'][$row['month']] = $row['total'];
    }
}

/** ------------------ JOBS (open vs closed) ------------------ */
$jobStats = ["Open" => 0, "Closed" => 0];
$sql = "SELECT status, COUNT(*) as total FROM company_jobs GROUP BY status";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $jobStats[$row['status']] = $row['total'];
}


/** ------------------ JOBS (open vs closed) ------------------ */
$jobStats = ["Open" => 0, "Closed" => 0];
$sql = "SELECT status, COUNT(*) as total FROM company_jobs GROUP BY status";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $jobStats[$row['status']] = $row['total'];
}

//bell icon

// fetch last 10 notifications for current admin
$adminUser = $_SESSION['username'];

$nsql = "SELECT id, message, link, is_read, created_at, type
         FROM notifications
         WHERE receiver_username = ?
         ORDER BY created_at DESC
         LIMIT 10";
$nstmt = $conn->prepare($nsql);
$nstmt->bind_param("s", $adminUser);
$nstmt->execute();
$notifications = $nstmt->get_result()->fetch_all(MYSQLI_ASSOC);
$nstmt->close();

// unread count
$csql = "SELECT COUNT(*) AS unread FROM notifications WHERE receiver_username = ? AND is_read = 0";
$cstmt = $conn->prepare($csql);
$cstmt->bind_param("s", $adminUser);
$cstmt->execute();
$unreadRow = $cstmt->get_result()->fetch_assoc();
$unread = (int)($unreadRow['unread'] ?? 0);
$cstmt->close();

// COMPANY STATS
// Company status overview
$sql = "SELECT status, COUNT(*) AS total 
        FROM users 
        WHERE role = 'company' 
        GROUP BY status";
$result = $conn->query($sql);

$companyStats = [
    'active' => 0,
    'banned' => 0
];

while ($row = $result->fetch_assoc()) {
    $companyStats[$row['status']] = (int)$row['total'];
}







?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://kit.fontawesome.com/a2e0e9c6b1.js" crossorigin="anonymous"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-900 text-white">

    <div class="flex h-screen">

        <!-- Sidebar -->
        <aside class="w-64 bg-slate-800 border-r border-slate-700 flex flex-col">
            <!-- Branding -->
            <div class="flex items-center justify-center h-16 border-b border-slate-700">
                <span class="text-xl font-bold">
                    <span class="text-indigo-400">Nexus</span><span class="text-purple-400">Career</span>
                </span>
            </div>



            <!-- Navigation -->
            <nav class="flex-1 px-4 py-6 space-y-2">
                <a href="admin_dashboard.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-slate-700 transition ">
                    <i class="fas fa-home mr-2"></i> Dashboard
                </a>
                <a href="manage_user.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-slate-700 transition">
                    <i class="fas fa-users mr-2"></i> Manage Users
                </a>
                <a href="manage_companies.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-slate-700 transition">
                    <i class="fas fa-building mr-2"></i> Manage Companies
                </a>
                <a href="manage_jobs.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-slate-700 transition">
                    <i class="fas fa-briefcase mr-2"></i> Manage Jobs
                </a>
                
                <a href="logout.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-red-700 transition">
                    <i class="fas fa-cog mr-2"></i> Logout
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6 overflow-y-auto">
            <h1 class="text-2xl font-bold mb-4">Welcome, Admin 👋</h1>
            <p class="text-gray-300">Here you can manage users, companies, and job postings.</p>

            <!-- bell icon -->
            <div class="flex items-center justify-end mb-4">
                <div class="relative">
                    <button id="bellBtn" class="relative px-3 py-2 bg-slate-800 rounded-xl hover:bg-slate-700">
                        <span class="text-xl">🔔</span>
                        <?php if (!empty($unread)): ?>
                            <span class="absolute -top-1 -right-1 bg-red-600 text-white text-xs px-1.5 rounded-full">
                                <?= (int)$unread ?>
                            </span>
                        <?php endif; ?>
                    </button>

                    <!-- Dropdown -->
                    <div id="bellDropdown" class="hidden absolute right-0 mt-2 w-80 bg-slate-800 border border-slate-700 rounded-xl shadow-xl z-50">
                        <?php if (empty($notifications)): ?>
                            <div class="p-4 text-sm text-gray-300">No notifications yet.</div>
                        <?php else: ?>
                            <div class="max-h-96 overflow-y-auto">
                                <?php foreach ($notifications as $n): ?>
                                    <a href="<?= htmlspecialchars($n['link'] ?? '#') ?>" class="block p-3 hover:bg-slate-700">
                                        <div class="text-sm text-white leading-snug"><?= $n['message'] ?></div>
                                        <div class="text-[11px] text-gray-400 mt-1"><?= htmlspecialchars($n['created_at']) ?></div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Dashboard Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                <!-- ✅ Users Graph Card -->
                <div class="p-6 bg-slate-800 rounded-xl shadow-lg">
                    <h2 class="text-lg font-semibold mb-2">Users Overview</h2>
                    <div class="h-40">
                        <canvas id="usersChart"></canvas>
                    </div>
                    <div class="mt-3 text-center">
                        <a href="manage_user.php"
                            class="px-3 py-1.5 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">
                            Explore Users
                        </a>
                    </div>
                </div>



                <!-- ✅ Companies Graph Card -->
                <div class="p-6 bg-slate-800 rounded-xl shadow-lg">
                    <h2 class="text-lg font-semibold mb-2">Companies Overview</h2>
                    <div class="h-40">
                        <canvas id="companiesChart"></canvas>
                    </div>
                    <div class="mt-3 text-center">
                        <a href="manage_companies.php"
                            class="px-3 py-1.5 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">
                            Explore Companies
                        </a>
                    </div>
                </div>

                <?php
                // Months labels
                $months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

                // Initialize arrays with 0 for each month
                $activeJobsMonthly = array_fill(1, 12, 0);
                $closedJobsMonthly = array_fill(1, 12, 0);

                // Query active jobs by month
                $sql = "SELECT MONTH(created_at) as month, COUNT(*) as total 
        FROM company_jobs 
        WHERE status = 'active' 
        GROUP BY MONTH(created_at)";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()) {
                    $activeJobsMonthly[(int)$row['month']] = (int)$row['total'];
                }

                // Query closed jobs by month
                $sql = "SELECT MONTH(created_at) as month, COUNT(*) as total 
        FROM company_jobs 
        WHERE status = 'closed' 
        GROUP BY MONTH(created_at)";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()) {
                    $closedJobsMonthly[(int)$row['month']] = (int)$row['total'];
                }
                ?>

                <!-- ✅ Jobs Graph Card -->
                <div class="p-6 bg-slate-800 rounded-xl shadow-lg">
                    <h2 class="text-lg font-semibold mb-2">Jobs Overview</h2>
                    <div class="h-40">
                        <canvas id="jobsChart"></canvas>
                    </div>
                    <div class="mt-3 text-center">
                        <a href="manage_jobs.php"
                            class="px-3 py-1.5 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">
                            Explore Jobs
                        </a>
                    </div>
                </div>


            </div>



    </div>
    </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Labels: months
        const labels = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

        // Users Chart (multi-line)
        new Chart(document.getElementById('usersChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                        label: 'Admins',
                        data: <?php echo json_encode(array_values($stats['admins'])); ?>,
                        borderColor: '#f43f5e', // red
                        backgroundColor: '#f43f5e',
                        tension: 0.4
                    },
                    {
                        label: 'Users',
                        data: <?php echo json_encode(array_values($stats['users'])); ?>,
                        borderColor: '#3b82f6', // blue
                        backgroundColor: '#3b82f6',
                        tension: 0.4
                    },
                    {
                        label: 'Companies',
                        data: <?php echo json_encode(array_values($stats['companies'])); ?>,
                        borderColor: '#22c55e', // green
                        backgroundColor: '#22c55e',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: '#fff',
                            font: {
                                size: 14
                            } // ✅ Bigger font
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: '#9ca3af',
                            font: {
                                size: 13
                            }
                        },
                        grid: {
                            color: '#374151'
                        }
                    },
                    y: {
                        ticks: {
                            color: '#9ca3af',
                            font: {
                                size: 13
                            }
                        },
                        grid: {
                            color: '#374151'
                        }
                    }
                }
            }
        });

        // Simple placeholder mini charts for Companies & Jobs
        new Chart(document.getElementById('companiesChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: ["Active", "Pending", "Banned"],
                datasets: [{
                    label: 'Companies',
                    data: [12, 5, 2], // replace with PHP query
                    backgroundColor: ['#22c55e', '#facc15', '#f87171']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        new Chart(document.getElementById('jobsChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: ["Open", "Closed"],
                datasets: [{
                    label: 'Jobs',
                    data: [30, 10], // replace with PHP query
                    backgroundColor: ['#3b82f6', '#ef4444']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>

    <!-- bell icon script -->
    <script>
        const bellBtn = document.getElementById('bellBtn');
        const bellDropdown = document.getElementById('bellDropdown');

        if (bellBtn) {
            bellBtn.addEventListener('click', () => {
                bellDropdown.classList.toggle('hidden');
            });
            // close on outside click
            document.addEventListener('click', (e) => {
                if (!bellBtn.contains(e.target) && !bellDropdown.contains(e.target)) {
                    bellDropdown.classList.add('hidden');
                }
            });
        }
    </script>

    <script>
        new Chart(document.getElementById('companiesChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: ["Active", "Banned"],
                datasets: [{
                    label: 'Companies',
                    data: <?php echo json_encode(array_values($companyStats)); ?>,
                    backgroundColor: ['#22c55e', '#f87171']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>

    <script>
        const jobLabels = <?php echo json_encode($months); ?>;

        new Chart(document.getElementById('jobsChart').getContext('2d'), {
            type: 'line', // or 'bar'
            data: {
                labels: jobLabels,
                datasets: [{
                        label: 'Active Jobs',
                        data: <?php echo json_encode($activeJobsMonthly); ?>,
                        borderColor: '#22c55e',
                        backgroundColor: '#22c55e',
                        tension: 0.4
                    },
                    {
                        label: 'Closed Jobs',
                        data: <?php echo json_encode($closedJobsMonthly); ?>,
                        borderColor: '#f43f5e',
                        backgroundColor: '#f43f5e',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: '#fff',
                            font: {
                                size: 14
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: '#9ca3af',
                            font: {
                                size: 13
                            }
                        },
                        grid: {
                            color: '#374151'
                        }
                    },
                    y: {
                        ticks: {
                            color: '#9ca3af',
                            font: {
                                size: 13
                            }
                        },
                        grid: {
                            color: '#374151'
                        }
                    }
                }
            }
        });
    </script>
<script>
    const jobLabels = <?php echo json_encode($months); ?>;
new Chart(document.getElementById('jobsChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: ["Open", "Closed"],
        datasets: [{
            label: 'Jobs',
            data: [30, 10], // replace with PHP query
            backgroundColor: ['#3b82f6', '#ef4444']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: {
                    color: '#fff',
                    font: { size: 14 }
                }
            }
        },
        scales: {
            x: {
                ticks: {
                    color: '#9ca3af',
                    font: { size: 13 }
                },
                grid: { color: '#374151' }
            },
            y: {
                ticks: {
                    color: '#9ca3af',
                    font: { size: 13 }
                },
                grid: { color: '#374151' }
            }
        }
    }
});
    </script>



</body>

</html>