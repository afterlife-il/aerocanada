<?php
require_once 'bootstrap.php';
require_auth();

require('../classes/company.class.php');

try {
    $objet = new company();
    $donnee = $objet->ajout_company(); // ← CORRECTION ICI !
    
    log_error("Company added successfully by " . $_SESSION['nom_utilisateur']);
    
    safe_redirect('company.php?companyrating=all');
    
} catch (Exception $e) {
    log_error("Error adding company: " . $e->getMessage(), [
        'user' => $_SESSION['nom_utilisateur'],
        'post_data' => $_POST
    ]);
    
    safe_redirect('ajout_company.php?error=1&msg=' . urlencode($e->getMessage()));
}
?>
```

---

## 🎯 RÉCAPITULATIF DES NOMS DE MÉTHODES

| Fichier | Classe | Méthode CORRECTE |
|---------|--------|------------------|
| valid_ajout_part.php | parts | `add_part()` ✅ |
| valid_company.php | company | `ajout_company()` ⚠️ |
| valid_ajout_contact_company.php | company | `ajout_contact_company()` ⚠️ |
| valid_ajout_stock.php | stock | `add_stock()` ✅ |

---

## 📋 TO-DO IMMÉDIAT
```
[ ] 1. Remplacer valid_ajout_contact_company.php (version ci-dessus)
[ ] 2. Remplacer valid_ajout_stock.php (version avec logs détaillés)
[ ] 3. Corriger valid_company.php (ajout_company au lieu de add_company)
[ ] 4. Tester ajout contact
[ ] 5. Tester ajout stock
[ ] 6. Me montrer les logs de _logs/app_errors.log