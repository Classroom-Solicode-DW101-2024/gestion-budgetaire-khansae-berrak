<?php
session_start();
if (!isset($_SESSION['user']['id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user']['id'];
$pdo = new PDO("mysql:host=localhost;dbname=gestion_budget", "root", "");


$selectedYear = $_GET['year'] ?? null;
$selectedMonth = $_GET['month'] ?? null;


$whereClause = "t.user_id = :userId";
$params = [':userId' => $userId];

if ($selectedYear && $selectedMonth) {
    $whereClause .= " AND YEAR(t.date_transaction) = :year AND MONTH(t.date_transaction) = :month";
    $params[':year'] = $selectedYear;
    $params[':month'] = $selectedMonth;
}
    
function getTotal($pdo, $userId, $type, $whereClause, $params) {
    $sql = "
        SELECT SUM(t.montant) AS total 
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        WHERE $whereClause AND c.type = :type
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_merge($params, [':type' => $type]));
    return $stmt->fetchColumn() ?: 0;
}

function getRecentTransactions($pdo, $whereClause, $params) {
    $sql = "
        SELECT t.*, c.nom AS categorie, c.type 
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        WHERE $whereClause
        ORDER BY t.date_transaction DESC
        LIMIT 5
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


$totalRevenu = getTotal($pdo, $userId, 'revenu', $whereClause, $params);
$totalDepense = getTotal($pdo, $userId, 'depense', $whereClause, $params);
$solde = $totalRevenu - $totalDepense;
$recentTransactions = getRecentTransactions($pdo, $whereClause, $params);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Gestion Budget</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f8f9fc;
            color: #444;
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #ff6b9d, #ff8cc8);
            color: white;
            padding: 25px 0;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .logo {
            font-size: 22px;
            font-weight: 700;
            text-align: center;
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            margin-bottom: 30px;
        }

        .nav-menu {
            display: flex;
            flex-direction: column;
            margin-top: 10px;
        }

        .nav-item {
            padding: 15px 25px;
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }

        .nav-item:hover, .nav-item.active {
            background: rgba(255,255,255,0.15);
            border-left: 4px solid white;
        }

        .nav-item i {
            margin-right: 10px;
        }

        .period-selector {
            margin: 25px;
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
            padding: 15px;
        }

        .period-selector select {
            width: 100%;
            background: white;
            border: none;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 10px;
            font-size: 14px;
            color: #555;
        }

        .period-selector button {
            width: 100%;
            background: #fff;
            color: #ff6b9d;
            border: none;
            padding: 10px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .period-selector button:hover {
            background: #f0f0f0;
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
        }

        .page-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 30px;
            color: #333;
        }

        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card h3 {
            font-size: 16px;
            color: #888;
            margin-bottom: 10px;
        }

        .stat-card p {
            font-size: 24px;
            font-weight: 700;
        }

        .revenus p {
            color: #4CAF50;
        }

        .depenses p {
            color: #f44336;
        }

        .solde p {
            color: #ff6b9d;
        }

        .transactions-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-header h2 {
            font-size: 18px;
            color: #444;
        }

        .transactions-table {
            width: 100%;
            border-collapse: collapse;
        }

        .transactions-table th {
            background-color: #f8f9fc;
            color: #666;
            font-weight: 600;
            text-align: left;
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }

        .transactions-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }

        .transactions-table tr:hover {
            background-color: #f8f9fc;
        }

        .tag {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .tag-revenu {
            background-color: rgba(76, 175, 80, 0.1);
            color: #4CAF50;
        }

        .tag-depense {
            background-color: rgba(244, 67, 54, 0.1);
            color: #f44336;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            color: #999;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                overflow: visible;
            }
            
            .logo {
                font-size: 18px;
                padding: 10px;
            }
            
            .nav-item span {
                display: none;
            }
            
            .period-selector {
                display: none;
            }
            
            .main-content {
                margin-left: 70px;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">Budget Manager</div>
        
        <nav class="nav-menu">
            <a href="#" class="nav-item active">
                <i>📊</i> <span>Dashboard</span>
            </a>
            <a href="#" class="nav-item">
                <i>💰</i> <span>Transactions</span>
            </a>
            <a href="#" class="nav-item">
                <i>📋</i> <span>Catégories</span>
            </a>
            <a href="#" class="nav-item">
                <i>📈</i> <span>Rapports</span>
            </a>
            <a href="#" class="nav-item">
                <i>⚙️</i> <span>Paramètres</span>
            </a>
        </nav>
        
        <div class="period-selector">
            <label>Année:</label>
            <select name="year">
                <option value="">-- Tous --</option>
                <option value="2025" selected>2025</option>
                <option value="2024">2024</option>
                <option value="2023">2023</option>
                <option value="2022">2022</option>
                <option value="2021">2021</option>
                <option value="2020">2020</option>
            </select>
            
            <label>Mois:</label>
            <select name="month">
                <option value="">-- Tous --</option>
                <option value="01">Janvier</option>
                <option value="02">Février</option>
                <option value="03">Mars</option>
                <option value="04" selected>Avril</option>
                <option value="05">Mai</option>
                <option value="06">Juin</option>
                <option value="07">Juillet</option>
                <option value="08">Août</option>
                <option value="09">Septembre</option>
                <option value="10">Octobre</option>
                <option value="11">Novembre</option>
                <option value="12">Décembre</option>
            </select>
            
            <button type="submit">Filtrer</button>
        </div>
    </div>

    <div class="main-content">
        <h1 class="page-title">Dashboard Financier</h1>
        
        <div class="dashboard-stats">
            <div class="stat-card revenus">
                <h3>Total Revenus</h3>
                <p>3,540.75 €</p>
            </div>
            <div class="stat-card depenses">
                <h3>Total Dépenses</h3>
                <p>2,184.50 €</p>
            </div>
            <div class="stat-card solde">
                <h3>Solde</h3>
                <p>1,356.25 €</p>
            </div>
        </div>

        <div class="transactions-section">
            <div class="section-header">
                <h2>Dernières Transactions</h2>
            </div>
            <table class="transactions-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Catégorie</th>
                        <th>Montant</th>
                        <th>Description</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="tag tag-revenu">Revenu</span></td>
                        <td>Salaire</td>
                        <td>2,500.00 €</td>
                        <td>Salaire mensuel</td>
                        <td>2025-04-15</td>
                    </tr>
                    <tr>
                        <td><span class="tag tag-depense">Dépense</span></td>
                        <td>Alimentation</td>
                        <td>89.75 €</td>
                        <td>Courses hebdomadaires</td>
                        <td>2025-04-22</td>
                    </tr>
                    <tr>
                        <td><span class="tag tag-depense">Dépense</span></td>
                        <td>Logement</td>
                        <td>850.00 €</td>
                        <td>Loyer Avril</td>
                        <td>2025-04-20</td>
                    </tr>
                    <tr>
                        <td><span class="tag tag-revenu">Revenu</span></td>
                        <td>Freelance</td>
                        <td>1,040.75 €</td>
                        <td>Projet client</td>
                        <td>2025-04-18</td>
                    </tr>
                    <tr>
                        <td><span class="tag tag-depense">Dépense</span></td>
                        <td>Transport</td>
                        <td>145.00 €</td>
                        <td>Essence</td>
                        <td>2025-04-16</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="footer">
            <p>&copy; 2025 MonApp. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>