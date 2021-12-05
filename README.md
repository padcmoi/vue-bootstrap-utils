# Component PHP for VueBootstrap


# ➡️Install
```
composer require padcmoi/vue-bootstrap-utils-for-php-api
```


# ➡️Usage
***Exemple Usage in a project***
```PHP
$SELECTOR = ["id", "created_at", "username"];
$SEARCHS_FILTER = ["id", "created_at", "username"];

$orderBy = VueBS4Table::orderBy(array_merge($SELECTOR, ['age']));
$limit = VueBS4Table::limit($args);
$filter = VueBS4Table::clauseForFilter($SEARCHS_FILTER, 'filter');

$get_age = ",YEAR(UTC_DATE()) - YEAR(date_naissance) - CASE WHEN MONTH(UTC_DATE()) < MONTH(date_naissance) OR (MONTH(UTC_DATE()) = MONTH(date_naissance) AND DAY(UTC_DATE()) < DAY(date_naissance)) THEN 1 ELSE 0 END AS age ";

$items = VueBS4Table::items(
    "SELECT DISTINCT " . implode(',', $SELECTOR) . $get_age . " " .
    "FROM users " .
    "WHERE " . $filter . $orderBy .
    "LIMIT :currentPage, :perPage ",
    [
        ['key' => ':filter', 'value' => VueBS4Table::filter(), 'param' => PDO::PARAM_STR],
        ['key' => ':currentPage', 'value' => $limit['limit'], 'param' => PDO::PARAM_INT],
        ['key' => ':perPage', 'value' => $limit['offset'], 'param' => PDO::PARAM_INT],
    ]
);

```

# ➡️Others
##### 🧳Packagist
https://packagist.org/packages/padcmoi/vue-bootstrap-utils-for-php-api

##### 🔖Licence
Ce travail est sous licence [MIT](/LICENSE).

##### 🔥Pour me contacter sur discord
Lien discord [discord.gg/257rUb9](https://discord.gg/257rUb9)

##### 🍺Si vous souhaitez m’offrir un pourboire
Me faire un don 😍 [par Paypal](https://www.paypal.com/paypalme/Julien06100?locale.x=fr_FR)
