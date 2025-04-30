<?php
function getCategories($pdo) {
    return [
        'revenu' => ['Salaire', 'Bourse', 'Ventes', 'Autres'],
        'depense' => ['Logement', 'Transport', 'Alimentation', 'Santé', 'Divertissement', 'Éducation', 'Autres']
    ];
}

function addTransaction($pdo, $userId, $categoryName, $categoryType, $amount, $description, $date) {
   
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE nom = ? AND type = ?");
    $stmt->execute([$categoryName, $categoryType]);
    $category = $stmt->fetch();

    if ($category) {
        $categoryId = $category['id'];
    } else {
        $stmt = $pdo->prepare("INSERT INTO categories (nom, type) VALUES (?, ?)");
        $stmt->execute([$categoryName, $categoryType]);
        $categoryId = $pdo->lastInsertId();
    }

    $stmt = $pdo->prepare("INSERT INTO transactions (user_id, category_id, montant, description, date_transaction) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $categoryId, $amount, $description, $date]);
}

function getTransactionById($pdo, $transactionId, $userId) {
    $stmt = $pdo->prepare("SELECT t.*, c.nom AS categorie_nom, c.type AS categorie_type 
                           FROM transactions t 
                           JOIN categories c ON t.category_id = c.id 
                           WHERE t.id = ? AND t.user_id = ?");
    $stmt->execute([$transactionId, $userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateTransaction($pdo, $transactionId, $userId, $categoryName, $categoryType, $amount, $description, $date) {
   
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE nom = ? AND type = ?");
    $stmt->execute([$categoryName, $categoryType]);
    $category = $stmt->fetch();

    if ($category) {
        $categoryId = $category['id'];
    } else {
        $stmt = $pdo->prepare("INSERT INTO categories (nom, type) VALUES (?, ?)");
        $stmt->execute([$categoryName, $categoryType]);
        $categoryId = $pdo->lastInsertId();
    }

    $stmt = $pdo->prepare("UPDATE transactions 
                           SET category_id = ?, montant = ?, description = ?, date_transaction = ? 
                           WHERE id = ? AND user_id = ?");
    $stmt->execute([$categoryId, $amount, $description, $date, $transactionId, $userId]);
}

function deleteTransaction($pdo, $transactionId, $userId) {
    $stmt = $pdo->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
    $stmt->execute([$transactionId, $userId]);
}
?>
