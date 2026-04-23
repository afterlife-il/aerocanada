<?php
require_once 'conf.php';

echo "<h1>Debug Classes</h1>";

// Company class
require('../classes/company.class.php');
$company = new company();
echo "<h2>Company Methods:</h2>";
echo "<pre>";
print_r(get_class_methods($company));
echo "</pre>";

// Stock class
require('../classes/stock.class.php');
$stock = new stock();
echo "<h2>Stock Methods:</h2>";
echo "<pre>";
print_r(get_class_methods($stock));
echo "</pre>";

// Parts class
require('../classes/parts.class.php');
$parts = new parts();
echo "<h2>Parts Methods:</h2>";
echo "<pre>";
print_r(get_class_methods($parts));
echo "</pre>";
?>
```

**Accédez à** : `https://aerocanada-industries.com/adminaero/pages/_debug_classes.php`

**Cela va vous montrer** TOUS les noms de méthodes disponibles dans chaque classe.

---

## 📋 TO-DO IMMÉDIAT
```
[ ] 1. Créer valid_ajout_contact_company.php (version hybride ci-dessus)
[ ] 2. Créer valid_ajout_stock.php (version hybride ci-dessus)
[ ] 3. Créer _debug_classes.php
[ ] 4. Accéder à _debug_classes.php et me montrer le résultat
[ ] 5. On corrigera avec les VRAIS noms de méthodes